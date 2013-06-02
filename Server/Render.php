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

    const BUFSIZE = 1024; // larger than display width => large enough

    protected $container;

    protected $heartbeat = self::HEARTBEAT_OPEN;
    protected $backlight = self::BACKLIGHT_OPEN;
    protected $titleSpeed = 1;
    protected $outputState = 0;

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
    public function screen($s, $timer)
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
    public function frame($list, $left, $top, $right, $bottom, $fwid, $fhgt, $fscroll, $fspeed, $timer)
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
                case Widget::WID_FRAME:
                    $newLeft = $left + $w->left - 1;
                    $newTop = $top + $w->top - 1;
                    $newRight = min($left + $w->right, $right);
                    $newBottom = min($top + $w->bottom, $bottom);
                    // Render only if it's visible...
                    if (($newLeft < $right) && ($newTop < $bottom)) {
                        $this->frame(
                            $w->frameScreen->widgetlist,
                            $newLeft,
                            $newTop,
                            $newRight,
                            $newBottom,
                            $w->width,
                            $w->height,
                            $w->length,
                            $w->speed,
                            $timer
                        );
                    }
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

    public function string($w, $left, $top, $right, $bottom, $fy)
    {
        if ($w instanceof Widget) {
            if ($w->text != null
                && $w->x > 0
                && $w->y > 0
                && $w->y >$fy
                && ($w->y <= $bottom - $top)) {

                $w->x = min($w->x, $right - $left);
                $length = min( ($right - $left) - $w->x + 1, self::BUFSIZE);
                $str = substr($w->text, 0, $length);
                $this->container->drivers->string($w->x + $left, $w->y + $top, $str);
            }
        }

        return 0;
    }

    public function hbar($w, $left, $top, $right, $bottom, $fy)
    {

    }

    public function vbar($w, $left, $top, $right, $bottom, $fy)
    {

    }

    public function title($w, $left, $top, $right, $bottom, $timer)
    {
        if (!$w instanceof Widget) {
            return;
        }

        $visWidth = $right - $left;
        if ($w->text != null && $visWidth >= 8) {
            $length = strlen($w->text);
            $width = $visWidth - 6;
            // calculate delay from titlespeed: <=0 -> 0, [1 - infty] -> [10 - 1]
            if ($this->titleSpeed <= self::TITLESPEED_NO) {
                $delay = self::TITLESPEED_NO;
            } else {
                $delay = max(self::TITLESPEED_MIN, self::TITLESPEED_MAX - $this->titleSpeed);
            }

            // display leading fillers
            $this->container->drivers->icon($w->x + $left, $w->y + $top, Drivers::ICON_BLOCK_FILLED);
            $this->container->drivers->icon($w->x + $left + 1, $w->y + $top, Drivers::ICON_BLOCK_FILLED);

            if (($length <= $width) || ($delay == 0)) {
                // copy test starting from the beginning
                $length = min($length, $width);
                $str = substr($w->text, 0, $length);
                // set x value for trailing fillers
                $x = $length + 4;
            } else {
                // Scroll the title, if it doesn't fit...
                $offset = $this->container->timer;

                // if the delay is "too large" increase cycle length
                if (($delay != 0) && ($delay < $length / ($length - $width))) {
                    $offset = $offset / $delay;
                }

                // reverse direction every length ticks
                $reverse = ($offset / $length) & 1;

                // restrict offset to cycle length
                $offset = $offset % $delay;
                $offset = max($offset, 0);

                // if the delay is "low enough" slow down as requested
                if (($delay != 0) && ($delay >= $length / ($length - $width))) {
                    $offset = $offset / $delay;
                }

                // restrict offset to the max. allowed offset: length - width
                $offset = min($offset, $length - $width);

                // scroll backward by mirroring offset at max. offset
                if ($reverse) {
                    $offset = ($length - $width) - $offset;
                }

                // copy test starting from offset
                $length = min($length, $width);
                $str = substr($w->text, $offset, $length);

                // set x value for trailing fillers
                $x = $visWidth - 2;
            }

            // display text
            $this->container->drivers->string($w->x + 3 + $left, $w->y + $top, $str);

            // display trailing fillers
            for ( ; $x < $visWidth; $x++) {
                $this->container->drivers->icon($w->x + $x + $left, $w->y + $top, Drivers::ICON_BLOCK_FILLED);
            }
        }

        return 0;
    }

    public function scroller($w, $left, $top, $right, $bottom, $timer)
    {

    }

    public function num($w, $left, $top, $right, $bottom)
    {
        // NOTE: y=10 means COLON (:)
        if (!$w instanceof Widget) {
            return -1;
        }

        if ( ($w->x > 0) && ($w->y >= 0) && ($w->y <= 10) ) {
            $this->container->drivers->num($w->x + $left, $w->y);
        }

    }

    public function msg($text, $expire)
    {
        if (strlen($text) > 15 || $expire <= 0) {
            return -1;
        }

        // Still a message active?
        if ($this->serverMsgExpire > 0) {
            $this->serverMsgText = '';
        }

        // Store new message
        $this->serverMsgText = $text . '| ';
        $this->serverMsgExpire = $expire;

        return 0;
    }
}
