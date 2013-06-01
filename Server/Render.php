<?php
namespace Theapi\Lcdproc\Server;

/**
 * This file contains code that actually generates the full screen data to
 * send to the LCD.
 */

use Theapi\Lcdproc\Server\Server;
use Theapi\Lcdproc\Server\Screen;
use Theapi\Lcdproc\Server\Widget;

class Render
{

    const HEARTBEAT_OFF  = 0;
    const HEARTBEAT_ON   = 1;
    const HEARTBEAT_OPEN = 2;

    const BACKLIGHT_OFF	  = 0;
    const BACKLIGHT_ON	  =	1;
    const BACKLIGHT_OPEN	=	2;
    const BACKLIGHT_BLINK	=	0x100;
    const BACKLIGHT_FLASH	=	0x200;

    const CURSOR_OFF        = 0;
    const CURSOR_DEFAULT_ON = 1;
    const CURSOR_BLOCK      = 4;
    const CURSOR_UNDER      = 5;

    const TITLESPEED_NO  = 0;	/* needs to be (TITLESPEED_MIN - 1) */
    const TITLESPEED_MIN = 1;
    const TITLESPEED_MAX = 10;

    protected $container;

    protected $heartbeat = self::HEARTBEAT_OPEN;
    protected $backlight = self::BACKLIGHT_OPEN;
    protected $titlespeed = self::BACKLIGHT_OPEN;
    protected $outputState = self::BACKLIGHT_OPEN;

    // If no heartbeat setting has been set at all
    protected $heartbeatFallback = self::HEARTBEAT_ON;

    // If no backlight setting has been set at all
    protected $backlightFallback = self::BACKLIGHT_ON;

    protected $serverMsgExpire = 0;
    protected $serverMsgText = '';

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Renders a screen. The following actions are taken in order:
     *
     * -  Clear the screen.
     * -  Set the backlight.
     * -  Set out-of-band data (output).
     * -  Render the frame contents.
     * -  Set the cursor.
     * -  Draw the heartbeat.
     * -  Show any server message.
     * -  Flush all output to screen.
     *
     * @param $s The screen to render.
     * @param $timer  A value increased with every call.
     * @return  -1 on error, 0 on success.
     */
    public static function screen($s, $timer)
    {
        if (!$s instanceof Screen) {
            return -1;
        }

        // 1. Clear the LCD screen...
        $this->container->drivers->clear();

        // 2. Set up the backlight
        // TODO: backlight

        // 3. Output ports from LCD - outputs depend on the current screen
        $this->container->drivers->output($this->outputState);

        // 4. Draw a frame...
        $this->frame(
            $s->widgetlist,
            0,
            0,
            $this->container->drivers->displayProps->width,
            $this->container->drivers->displayProps->height,
            $s->width,
            $s->height,
            'v',
            max($s->duration / $s->height, 1),
            $this->container->timer
        );

        // 5. Set the cursor
        $this->container->drivers->cursor($s->cursor_x, $s->cursor_y, $s->cursor);

        // 6. Set the heartbeat
        if ($this->heartbeat != self::HEARTBEAT_OPEN) {
            $tmpState = $this->heartbeat;
        } elseif (($s->client != NULL) && ($s->client->heartbeat != self::HEARTBEAT_OPEN)) {
            $tmpState = $s->client->heartbeat;
        } elseif ($s->heartbeat != self::HEARTBEAT_OPEN) {
            $tmpState = $s->heartbeat;
        } else {
            $tmpState = $this->heartbeatFallback;
        }
        $this->container->drivers->heartbeat($tmpState);

        // 7. If there is an server message that is not expired, display it
        if ($this->serverMsgExpire > 0) {
            // at bottom right
            $this->container->drivers->string(
                $this->container->drivers->width - strlen($this->serverMsgText) + 1,
                $this->container->drivers->height,
                $this->serverMsgText
            );
        }

        // 8. Flush display out, frame and all...
        $this->container->drivers->flush();

        return 0;
    }

    /**
     * Not just frames...
     *
     * @param array $list
     * @param int $left    left edge of frame
     * @param int $top     top edge of frame
     * @param int $right   right edge of frame
     * @param int $bottom  bottom edge of frame
     * @param int $fwid    frame width?
     * @param int $fhgt    frame height?
     * @param int $fscroll direction of scrolling
     * @param int $fspeed  speed of scrolling...
     * @param int $timer   current timer tick
     */
    public static function frame($list, $left, $top, $right, $bottom, $fwid, $fhgt, $fscroll, $fspeed, $timer)
    {
        // Scrolling offset for the frame...
        $fy = 0;

        // return on no data or illegal height
        if (empty($list) || ($fhgt <= 0)) {
            return -1;
        }

        // vertical scrolling
        if ($fscroll == 'v') {
            // only set offset !=0 when fspeed is != 0 and there is something to scroll
            if (($fspeed != 0) && ($fhgt > $bottom - $top)) {
                $fy_max = $fhgt - ($bottom - $top) + 1;

                if ($fspeed > 0) {
                    $fy = ($timer / $fspeed) % $fy_max;
                } else {
                    $fy = (-1 * $fspeed * $timer) % $fy_max;
                }
                // safeguard against negative values
                $fy = max($fy, 0);
            }
        }
        // Frames don't scroll horizontally

        // reset widget list
        rest($list);

        // loop over all widgets
        foreach ($list as $w) {
            if (!$w instanceof Widget) {
                //TODO: better error reporting than var_dump
              var_dump($w);
              continue;
            }

            switch ($w->type) {
                case Widget::WID_STRING:
                    $this->string($w, $left, $top - $fy, $right, $bottom, $fy);
                    break;
                case Widget::WID_HBAR:
                    $this->hbar($w, $left, $top - $fy, $right, $bottom, $fy);
                    break;
                case Widget::WID_VBAR:
                    $this->vbar($w, $left, $top, $right, $bottom);
                    break;
                case Widget::WID_ICON:
                    $this->container->drivers->icon($w->x, $w->y, $w->length);
                    break;
                case Widget::WID_TITLE:
                    $this->title($w, $left, $top, $right, $bottom, $timer);
                    break;
                case Widget::WID_SCROLLER:
                    $this->scroller($w, $left, $top, $right, $bottom, $timer);
                    break;
                case Widget::WID_NUM:
                    // NOTE: y=10 means COLON (:)
                    if (($w->x > 0) && ($w->y >= 0) && ($w->y <= 10)) {
                        $this->container->drivers->num($w->x + $left, $w->y);
                    }
                    break;
                case Widget::WID_NONE:
                default:
                    // nothing
                    break;
            }
        }

        return 0;
    }

    public static function string($w, $left, $top, $right, $bottom, $fy)
    {
        if ($w instanceof Widget) {
            if ($w->text != null
                && $w->x > 0
                && $w->y > 0
                && $w->y >$fy
                && ($w->y <= $bottom - $top) {

                $w->x = min($w->x, $right - $left);
                $length = min($right - $left - $w->x + 1, 0);
                $str = substr($w->x, 0, $length);
                $this->container->drivers->string($w->x + $left, $w->y + $top, $str);

            }
        }

        return 0;
    }

    public static function hbar($w, $left, $top, $right, $bottom, $fy)
    {

    }

    public static function vbar($w, $left, $top, $right, $bottom, $fy)
    {

    }

    public static function title($w, $left, $top, $right, $bottom, $timer)
    {

    }

    public static function scroller($w, $left, $top, $right, $bottom, $timer)
    {

    }

    public static function num($w, $left, $top, $right, $bottom)
    {

    }

    public static function msg($text, $expire)
    {

    }
}
