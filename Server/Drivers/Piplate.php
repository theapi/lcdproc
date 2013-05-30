<?php
namespace Theapi\Lcdproc\Server\Drivers;

class Piplate
{

    protected $width = 16;
    protected $height = 2;
    protected $cellWidth = 5;
    protected $cellHeight = 5;

    protected $debug = 0;

    protected $disabled = true; // so I can test without the Pi on

    // Two rows of 16 spaces
    protected $outBlank = array('                ', '                ');
    protected $out = array();
    protected $fp;

    /**
     * Initialize the driver.
     */
    public function __construct()
    {
        // connect to the socket that the python script is listening on

        $this->server = '192.168.0.11';
        $this->port = 8888;


        if (!$this->disabled) {
            $this->fp = stream_socket_client('tcp://' . $this->server . ':' . $this->port, $errno, $errstr, 30);

            if (!$this->fp) {
                throw new \Exception('Unable to connect to ' . $this->server . ':' . $this->port, $errno, 0);
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

    public function doesOutput()
    {
        return true;
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
     * Clear the screen.
     */
    public function clear()
    {
        // Reset to the blank screen array
        $this->out = $this->outBlank;
        $this->flush();
    }

    /**
     * Flush data on screen to the display.
     */
    public function flush()
    {
        $string = join("\n", $this->out);

        try {
            $this->write($string);
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
        // Our positions start with 0 not 1
        $x--;
        $y--;

        $len = strlen($string);
        for ($i =0; $i < $len; $i++) {
            $pos = $x + $i;
            $this->chr($pos, $y, $string[$i]);
        }
    }

    /**
     * Print a character on the screen at position (x,y).
     *
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param chr   String that gets written.
     */
    public function chr($x, $y, $chr)
    {
        $this->out[$y][$x] = $chr;
    }

    public function vbar($x, $y, $len, $promille, $pattern)
    {

    }

    public function hbar($x, $y, $len, $promille, $pattern)
    {

    }

    /**
     * Write a big number.
     * @param x        Horizontal character position (column).
     * @param num      Character to write (0 - 10 with 10 representing ':')
     */
    public function num($x, $num)
    {

    }

    /**
     * Perform heartbeat.
     * @param state    Heartbeat state.
     */
    public function heartbeat($state)
    {

    }

    /**
     * Write icon.
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param icon     synbolic value representing the icon.
     */
    public function icon($x, $y, $icon)
    {

    }

    public function cursor($x, $y, $state)
    {

    }

    /**
     * Turn the display backlight on or off.
     *
     * @param state    New backlight status.
     */
    public function backlight($state)
    {

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
            foreach(explode("\n", $buf) as $line) echo " > $line\n";
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
