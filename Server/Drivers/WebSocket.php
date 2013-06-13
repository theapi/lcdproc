<?php
namespace Theapi\Lcdproc\Server\Drivers;


use Theapi\Lcdproc\Server\Driver as Driver;
use Theapi\Lcdproc\Server\Render;

class WebSocket extends Driver
{

    protected $debug = 0;

    protected $disabled = false; // so I can test without the server running

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

        $this->server = 'localhost'; // TODO: make websocket server configurable
        $this->port = 8080; // TODO: make websocket port configurable




        // Set dimensions
        $this->width = 16;
        $this->height = 2;
        $this->cellWidth = 5;
        $this->cellHeight = 5;

        if (!$this->disabled) {
            $this->ws = new Client(); // AHHHHH! no working php client, need another way. Do I really want ZeroMq just for this??
            if ($this->ws->connect($this->server, $this->port, '')) {
                $container->log(LOG_INFO, 'Connected to websocket at '. $this->server);
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


    public function write($buf)
    {
        $this->container->log(LOG_DEBUG, 'write > '. $buf);

        if ($this->disabled) {
            return;
        }

        if (!$this->ws->sendData($buf)) {
            throw new \Exception('Failed to send to websocket');
        }
    }

    public function disconnect()
    {

    }
}
