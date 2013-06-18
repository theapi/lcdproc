<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Render;
use Theapi\Lcdproc\Server\Drivers;
use Theapi\Lcdproc\Server\Widget;

/**
 * This is the the base driver class.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

abstract class Driver
{

    protected $container;

    protected $width;
    protected $height;
    protected $cellWidth;
    protected $cellHeight;

    abstract public function doesOutput();
    abstract public function doesInput();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function width()
    {
        return $this->width;
    }

    public function height()
    {
        return $this->height;
    }

    public function cellWidth()
    {
        return $this->cellWidth;
    }

    public function cellHeight()
    {
        return $this->cellHeight;
    }

    /**
     * Draw a vertical bar bottom-up.
     * @param $x        Horizontal character position (column) of the starting point.
     * @param $y        Vertical character position (row) of the starting point.
     * @param $len      Number of characters that the bar is high at 100%
     * @param $promille Current height level of the bar in promille.
     * @param $options  Options (currently unused).
     */
    public function vbar($x, $y, $len, $promille, $pattern)
    {
        // if the driver does not support output, do nothing
        if (!method_exists($this, 'chr')) {
            return;
        }

        for ($pos = 0; $pos < $len; $pos++) {
            if (2 * $pos < ($promille * $len / 500 + 1)) {
                $this->chr($x, $y - $pos, '|');
            }
        }
    }

    /**
     * Draw a horizontal bar bottom-up.
     * @param $x        Horizontal character position (column) of the starting point.
     * @param $y        Vertical character position (row) of the starting point.
     * @param $len      Number of characters that the bar is high at 100%
     * @param $promille Current length level of the bar in promille.
     * @param $options  Options (currently unused).
     */
    public function hbar($x, $y, $len, $promille, $pattern)
    {
        // if the driver does not support output, do nothing
        if (!method_exists($this, 'chr')) {
            return;
        }

        for ($pos = 0; $pos < $len; $pos++) {
            if (2 * $pos < ($promille * $len / 500 + 1)) {
                $this->chr($x + $pos, $y, '-');
            }
        }
    }

    /**
     * Write a big number.
     * @param x        Horizontal character position (column).
     * @param num      Character to write (0 - 10 with 10 representing ':')
     */
    public function num($x, $num)
    {
        // Mmm...
    }

    /**
     * Perform heartbeat.
     * @param state    Heartbeat state.
     */
    public function heartbeat($state)
    {
        if ($state == Render::HEARTBEAT_OFF) {
            // Don't display anything
            return;
        }

        // if the driver does not have a width, do nothing
        if (empty($this->width)) {
            // Don't display anything
            return;
        }

        if ($this->container->timer & 5) {
            $icon = Widget::ICON_HEART_FILLED;
        } else {
            $icon = Widget::ICON_HEART_OPEN;
        }

        $this->icon($this->width, 1, $icon);
    }

    /**
     * Place an icon on the screen.
     * @param $x        Horizontal character position (column).
     * @param $y        Vertical character position (row).
     * @param $icon     synbolic value representing the icon.
     */
    public function icon($x, $y, $icon)
    {
        // if the driver does not support output, do nothing
        if (!method_exists($this, 'chr')) {
            return;
        }

        switch ($icon) {
          case Widget::ICON_BLOCK_FILLED:
              $ch1 = '#';
              break;
          case Widget::ICON_HEART_OPEN:
              $ch1 = '-';
              break;
          case Widget::ICON_HEART_FILLED:
              $ch1 = '#';
              break;
          case Widget::ICON_ARROW_UP:
              $ch1 = '^';
              break;
          case Widget::ICON_ARROW_DOWN:
              $ch1 = 'v';
              break;
          case Widget::ICON_ARROW_LEFT:
              $ch1 = '<';
              break;
          case Widget::ICON_ARROW_RIGHT:
              $ch1 = '>';
              break;
          case Widget::ICON_CHECKBOX_OFF:
              $ch1 = 'N';
              break;
          case Widget::ICON_CHECKBOX_ON:
              $ch1 = 'Y';
              break;
          case Widget::ICON_CHECKBOX_GRAY:
              $ch1 = 'o';
              break;
          case Widget::ICON_SELECTOR_AT_LEFT:
              $ch1 = '>';
              break;
          case Widget::ICON_SELECTOR_AT_RIGHT:
              $ch1 = '<';
              break;
          case Widget::ICON_ELLIPSIS:
              $ch1 = '_';
              break;
          case Widget::ICON_STOP:
              $ch1 = '[';
              $ch2 = ']';
              break;
          case Widget::ICON_PAUSE:
              $ch1 = '|';
              $ch2 = '|';
              break;
          case Widget::ICON_PLAY:
              $ch1 = '>';
              $ch2 = ' ';
              break;
          case Widget::ICON_PLAYR:
              $ch1 = '<';
              $ch2 = ' ';
              break;
          case Widget::ICON_FF:
              $ch1 = '>';
              $ch2 = '>';
              break;
          case Widget::ICON_FR:
              $ch1 = '<';
              $ch2 = '<';
              break;
          case Widget::ICON_NEXT:
              $ch1 = '>';
              $ch2 = '|';
              break;
          case Widget::ICON_PREV:
              $ch1 = '|';
              $ch2 = '<';
              break;
          case Widget::ICON_REC:
              $ch1 = '(';
              $ch2 = ')';
              break;
        }

        $this->chr($x, $y, $ch1);
        if (isset($ch2)) {
        	$this->chr($x+1, $y, $ch2);
        }
    }

    /**
     * Set cursor position and state.
     *
     * @param $x      Horizontal cursor position (column).
     * @param $y      Vertical cursor position (row).
     * @param $state  New cursor state.
     */
    public function cursor($x, $y, $state)
    {
        // if the driver does not support output, do nothing
        if (!method_exists($this, 'chr')) {
            return;
        }

        switch ($state) {
            case Render::CURSOR_BLOCK:
            case Render::CURSOR_DEFAULT_ON:
                if ($this->container->timer & 2) {
                    $this->icon($x, $y, Widget::ICON_BLOCK_FILLED);
                }
                break;
            case Render::CURSOR_UNDER:
                if ($this->container->timer & 2) {
                    $this->chr($x, $y, '_');
                }
                break;
        }
    }
}
