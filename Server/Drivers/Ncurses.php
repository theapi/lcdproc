<?php
namespace Theapi\Lcdproc\Server\Drivers;

use Theapi\Lcdproc\Server\Driver as Driver;
use Theapi\Lcdproc\Server\Render;

/**
 * sudo apt-get install libncurses5-dev
 * sudo pecl install ncurses
 *
 */

/*
   ncurses_init();
   ncurses_curs_set(0);

   ncurses_start_color();
ncurses_init_pair(1, NCURSES_COLOR_YELLOW, NCURSES_COLOR_BLUE);
    //ncurses_bkgdset(NCURSES_COLOR_YELLOW);

   $screen = ncurses_newwin( 0, 0, 0, 0);
   ncurses_wborder($screen, 0,0, 0,0, 0,0, 0,0);
ncurses_wcolor_set ( $screen , 1 );

ncurses_mvwaddstr($screen, 1, 1, "Hello world! Yellow on blue text!");



   ncurses_wrefresh($screen);
   while(1) {

   }
   ncurses_end();

*/

class Ncurses extends Driver
{

    protected $outBlank = array(
        '1' => '                 ',
        '2' => '                 ',
    );
    protected $out = array();
    protected $backlightState;

    /**
     * Initialize the driver.
     */
    public function __construct($container)
    {
        parent::__construct($container);

        // Set dimensions
        $this->width = 16;
        $this->height = 2;
        $this->cellWidth = 5;
        $this->cellHeight = 5;

        ncurses_init();
        ncurses_curs_set(0);
        ncurses_start_color();
        ncurses_init_pair(1, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK); // backlight off
        ncurses_init_pair(2, NCURSES_COLOR_BLACK, NCURSES_COLOR_WHITE); // backlight on
        $this->screen = ncurses_newwin( $this->height +2, $this->width+2, 0, 0);
        ncurses_wborder($this->screen, 0, 0, 0, 0, 0, 0, 0, 0);
        ncurses_wcolor_set($this->screen, 1);
        ncurses_wrefresh($this->screen);


    }

    /**
     * Close the driver (do necessary clean-up).
     */
    public function close()
    {
        ncurses_end();
    }

    public function doesOutput() {
        return true;
    }

    public function doesInput() {
        return false; // not yet
    }

    /**
     * Clear the screen.
     */
    public function clear()
    {
        // Reset to the blank screen array
        $this->out = $this->outBlank;

        // no need to call clear on the python end as the whole
        // screen gets rendered
    }

    /**
     * Flush data on screen to the display.
     */
    public function flush()
    {
        // remove the first column
        // as it was there just because lcdproc x & y start at 1
        $line1 = substr($this->out[1], 1);
        $line2 = substr($this->out[2], 1);

        ncurses_mvwaddstr($this->screen, 1, 1, $line1);
        ncurses_mvwaddstr($this->screen, 2, 1, $line2);

        ncurses_wrefresh($this->screen);

        // Reset to the blank screen array
        $this->out = $this->outBlank;
    }

    /**
     * Print a string on the screen at position (x,y).
     *
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param string   String that gets written.
     */
    public function string($x, $y, $string)
    {
        $len = strlen($string);
        for ($i =0; $i < $len; $i++) {
            $pos = $x + $i;
            $this->chr($pos, $y, $string[$i]);
        }
    }

    /**
     * Print a character on the screen at position (x,y).
     *
     * NB: ACSII only :(
     *
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param chr   String that gets written.
     */
    public function chr($x, $y, $chr)
    {
        $this->out[$y][$x] = $chr;
    }

    /**
     * Write a big number.
     * @param x        Horizontal character position (column).
     * @param num      Character to write (0 - 10 with 10 representing ':')
     */
    public function num($x, $num)
    {
        // Mmm, big numbers in 2 lines with ascii...

        // not so big for now
        if ($num == 10) {
          $chr = ':';
        } else {
          $chr = $num;
        }
        $this->chr($x, 1, $chr);
    }

    /**
     * Turn the display backlight on or off.
     *
     * @param state    New backlight status.
     */
    public function backlight($state)
    {
        if ($this->backlightState !== $state) {
            $this->backlightState = $state;
            if ($state == 1) {
                // on
                ncurses_wcolor_set($this->screen, 2);
            } else {
                // off
                ncurses_wcolor_set($this->screen, 1);
            }
            $this->flush();
        }
    }

    /**
     * Get key presses.
     */
    public function getKey()
    {

    }

    /**
     * Provide some information about this driver.
     */
    public function getInfo()
    {
        return 'Ncurses driver';
    }

}
