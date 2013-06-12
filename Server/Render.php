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

    // Patterns for hbar / vbar, mostly (if not all) UNIMPLEMENTED
    const BAR_POS = 0x001; /* default
				 Promilles allowed: 0 to 1000
				 The zero-point is at the left or bottom */
    const BAR_NEG = 0x002; /* the bar grows in negative direction
				 Promilles allowed: -1000 to 0
				 The zero-point is at the left or top */
    const BAR_POS_AND_NEG = 0x003; /* the bars can grow in both directions
				 Promilles allowed: -1000 to 1000
				 The zero-point is in the center */
    const BAR_PATTERN_FILLED  = 0x000; /* default */
    const BAR_PATTERN_OPEN    = 0x010;
    const BAR_PATTERN_STRIPED = 0x020;
    const BAR_WITH_PERCENTAGE = 0x100;

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
    protected $backlight;
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
        $this->displayProps = $this->container->drivers->displayProps;
        $this->backlight = $this->container->config->backlight;
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

        // TODO: handle colours!

        /*
         * 2.1:
         * First we find out who has set the backlight:
         *   a) the screen,
         *   b) the client, or
         *   c) the server core
         * with the latter taking precedence over the earlier. If the
         * backlight is not set on/off then use the fallback (set it ON).
        */
        if ($this->backlight != Render::BACKLIGHT_OPEN) {
            // from the config
            $tmpState = $this->backlight;
        } elseif (($s->client != null) && ($s->client->backlight != Render::BACKLIGHT_OPEN)) {
            $tmpState = $s->client->backlight;
        } elseif ($s->backlight != Render::BACKLIGHT_OPEN) {
            $tmpState = $s->backlight;
        } else {
            $tmpState = $this->backlightFallback;
        }

        /*
         * 2.2:
         * If one of the backlight options (FLASH or BLINK) has been set turn
         * it on/off based on a timed algorithm.
         */
        // NOTE: dirty stripping of other options...
        if ($tmpState & Render::BACKLIGHT_FLASH) {
            // Backlight flash: check timer and flip backlight as appropriate
            if ( ($tmpState & Render::BACKLIGHT_ON) ^ (($this->container->timer & 7) == 7) ) {
                $state = Render::BACKLIGHT_ON;
            } else {
                $state = Render::BACKLIGHT_OFF;
            }
            $this->container->drivers->backlight($state);
        } elseif ($tmpState & Render::BACKLIGHT_BLINK) {
            // Backlight blink: check timer and flip backlight as appropriate
            if ( ($tmpState & Render::BACKLIGHT_ON) ^ (($this->container->timer & 14) == 14) ) {
                $state = Render::BACKLIGHT_ON;
            } else {
                $state = Render::BACKLIGHT_OFF;
            }
            $this->container->drivers->backlight($state);
        } else {
            // Simple: Only send lowest bit then...
            $this->container->drivers->backlight($tmpState & Render::BACKLIGHT_ON);
        }

        // 3. Output ports from LCD - outputs depend on the current screen
        $this->container->drivers->output($this->outputState);

        // 4. Draw a frame...
        $this->frame(
            $s->widgetlist,
            0,
            0,
            $this->displayProps->width,
            $this->displayProps->height,
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
                $this->container->log(LOG_DEBUG, print_r($w, true));
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
        if (!$w instanceof Widget) {
            return;
        }

        if (($w->x > 0) && ($w->y > 0) && ($w->y > $fy) && ($w->y <= $bottom - $top)) {
            if ($w->length > 0) {
                $fullLen = $this->displayProps->width - $w->x - $left + 1;
                $promille = 1000;

                if (($w->length / $this->displayProps->cellWidth) < $right - $left - $w->x + 1) {
                    $promille = 1000 * $w->length / ($this->displayProps->cellWidth * $fullLen);
                }

                $this->container->drivers->hbar(
                    $w->x + $left,
                    $w->y + $top,
                    $fullLen,
                    $promille,
                    self::BAR_PATTERN_FILLED
                );
            }
        }

        return 0;
    }

    public function vbar($w, $left, $top, $right, $bottom)
    {
        if (!$w instanceof Widget) {
            return;
        }

        if (($w->x > 0) && ($w->y > 0)) {
            if ($w->length > 0) {
                $fullLen = $this->displayProps->height;
                $promille = 1000 * $w->length / ($this->displayProps->cellHeight * $fullLen);
                $this->container->drivers->vbar(
                    $w->x + $left,
                    $w->y + $top,
                    $fullLen,
                    $promille,
                    self::BAR_PATTERN_FILLED
                );
            }
        }

        return 0;
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
            $this->container->drivers->icon($w->x + $left, $w->y + $top, Widget::ICON_BLOCK_FILLED);
            $this->container->drivers->icon($w->x + $left + 1, $w->y + $top, Widget::ICON_BLOCK_FILLED);

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
                $this->container->drivers->icon($w->x + $x + $left, $w->y + $top, Widget::ICON_BLOCK_FILLED);
            }
        }

        return 0;
    }

    public function scroller($w, $left, $top, $right, $bottom, $timer)
    {
        if (!$w instanceof Widget) {
            return;
        }

        if (($w->text != null) && ($w->right >= $w->left)) {
            $str =
            $screenWidth = abs($w->right - $w->left + 1);
            $screenWidth = min($screenWidth, strlen($w->text)-1);
        }

        switch ($w->length) { // actually, direction...
            case 'm': // Marquee
                $length = strlen($w->text);
                if ($length <= $screenWidth) {
                    // it fits within the box, just render it
                    $this->container->drivers->string($w->left, $w->top, $w->text);
                } else {
                    $necessaryTimeUnits = 0;

                    if ($w->speed > 0) {
                        $necessaryTimeUnits = $length * $w->speed;
                        $offset = ($this->container->timer % $necessaryTimeUnits) / $w->speed;
                    } elseif ($w->speed < 0) {
                        $necessaryTimeUnits = $length / ($w->speed * -1);
                        $offset = ($this->container->timer % $necessaryTimeUnits) * $w->speed * -1;
                    } else {
                        $offset = 0;
                    }

                    if ($offset <= $length) {
                        $room = $screenWidth - ($length - $offset);
                        $str = substr($w->text, $offset, $screenWidth);

                        // if there's more room, restart at the beginning
                        if ($room > 0) {
                            $str .= substr($w->text, 0, $room);
                        }
                    } else {
                       $str = '';
                    }
                    $this->container->drivers->string($w->left, $w->top, $str);
                }
                break;
            case 'h':
                $length = strlen($w->text) + 1;
                if ($length <= $screenWidth) {
                    // it fits within the box, just render it
                    $this->container->drivers->string($w->left, $w->top, $w->text);
                } else {
                    $effLength = $length - $screenWidth;
                    $necessaryTimeUnits = 0;
                    if ($w->speed > 0) {
                        $necessaryTimeUnits = $effLength - $w->speed;
                        if ((($this->container->timer / $necessaryTimeUnits) % 2) == 0) {
                            // wiggle one way
                            $offset = ($this->container->timer % ($effLength * $w->speed)) / $w->speed;
                        } else {
                            // wiggle the other
                            $offset = ((($this->container->timer % ($effLength * $w->speed)) - ($effLength * $w->speed) + 1) / $w->speed) * -1;
                        }
                    } elseif ($w->speed < 0) {
                        $necessaryTimeUnits = $effLength / ($w->speed * -1);
                        if ((($this->container->timer / $necessaryTimeUnits) % 2) == 0) {
                            $offset = ($this->container->timer % ($effLength / ($w->speed * -1))) * $w->speed * -1;
                        } else {
                            $offset = ((($this->container->timer % ($effLength / ($w->speed * -1))) * $w->speed * -1) - $effLength + 1) * -1;
                        }
                    } else {
                        $offset = 0;
                    }

                    if ($offset <= $length) {
                        $str = substr($w->text, $offset, $screenWidth);
                    } else {
                       $str = '';
                    }
                    $this->container->drivers->string($w->left, $w->top, $str);
                }
                break;
        }

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
