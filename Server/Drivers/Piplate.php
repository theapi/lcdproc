<?php
namespace Theapi\Lcdproc\Server\Drivers;

use Theapi\Lcdproc\Server\Driver as Driver;
use Theapi\Lcdproc\Server\Render;

// TODO: move the outputing code to the python script at the other end of the socket
// as it has functions for chr, string, scroll, cursor etc.
class Piplate extends Driver
{

    protected $debug = 0;

    protected $disabled = false; // so I can test without the Pi on

    // Two rows of 16(7) spaces (column 0 gets stripped later leaving 16)
    //  $outBlank[1] & $outBlank[2]
    // index starting at one because that's what lcdproc expects
    protected $outBlank = array(
        '1' => '                 ',
        '2' => '                 ',
    );
    protected $out = array();
    protected $fp;
    protected $backlightState;

    /**
     * Initialize the driver.
     */
    public function __construct($container)
    {
        parent::__construct($container);

        $this->server = '192.168.0.11'; // TODO: make pi server configurable
        $this->port = 8888;

        // Set dimensions
        $this->width = 16;
        $this->height = 2;
        $this->cellWidth = 5;
        $this->cellHeight = 5;

        if (!$this->disabled) {
            // connect to the socket that the python script is listening on
            $this->fp = stream_socket_client('tcp://' . $this->server . ':' . $this->port, $errno, $errstr, 30);

            if (!$this->fp) {
                throw new \Exception('Unable to connect to ' . $this->server . ':' . $this->port, $errno);
            }
        }

        // Setup the array of spaces
        $this->out = $this->outBlank;
    }

    /**
     * Close the driver (do necessary clean-up).
     */
    public function close()
    {
        // close socket to python script

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
        try {
            // prepend the message with "message:"
            $this->write("message:$line1\n$line2");
            // read just to clear the memory
            $this->read();
        } catch (\Exception $e) {
            if ($e->getCode() == 0) {
                // no connection
                // try later etc...

                // for now, give up
                throw $e;
            }
        }

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
            $this->write("backlight:$state");
            // read just to clear the memory
            $this->read();
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
        return 'Adafruit pilate driver';
    }

    public function read()
    {
        if ($this->disabled) {
            return;
        }

        if (!$this->fp) {
            throw new \Exception('No connection to ' . $this->server . ':' . $this->port);
        }

        $line = fgets($this->fp);

        if ($this->debug > 2) {
            $info = stream_get_meta_data($this->fp);
            echo " < $line".($info['timed_out'] ? " read timed out" : "")."\n";
        }

        if ($this->debug > 1) {
            echo " < $line\n";
        }

        return $line;
    }

    public function write($buf)
    {
        if ($this->disabled) {
            return;
        }

        if (!$this->fp) {
            throw new \Exception('No connection to ' . $this->server . ':' . $this->port, 0);
        }

        $info = stream_get_meta_data($this->fp);
        $alive = !$info['eof'] && !$info['timed_out'];
        if (!$alive) {
            throw new \Exception('Lost connection to ' . $this->server . ':' . $this->port, 0);
        }

        if ($this->debug > 1) {
            foreach (explode("\n", $buf) as $line) {
                echo " > $line\n";
            }
        }
        @fwrite($this->fp, "$buf\n");

    }

    public function disconnect()
    {
        if ($this->debug > 1) {
            echo ">< Disconnecting from LCDd\n";
        }

        $this->write('bye');
        fclose($this->fp);

        if ($this->debug > 1) {
            echo ">< Disconnected!\n";
        }
    }
}
