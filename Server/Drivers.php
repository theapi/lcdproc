<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Drivers\Piplate;

/**
 * Manage the lists of loaded drivers and perform actions on all drivers.
 */


class Drivers
{

    // Icons below are one character wide
    const ICON_BLOCK_FILLED	 = 0x100;
    const ICON_HEART_OPEN    = 0x108;
    const ICON_HEART_FILLED  = 0x109;
    const ICON_ARROW_UP      = 0x110;
    const ICON_ARROW_DOWN    = 0x111;
    const ICON_ARROW_LEFT    = 0x112;
    const ICON_ARROW_RIGHT   = 0x113;
    const ICON_CHECKBOX_OFF  = 0x120;
    const ICON_CHECKBOX_ON   = 0x121;
    const ICON_CHECKBOX_GRAY = 0x122;
    const ICON_SELECTOR_AT_LEFT	  = 0x128;
    const ICON_SELECTOR_AT_RIGHT	= 0x129;
    const ICON_ELLIPSIS      = 0x130;

    // Icons below are two characters wide
    const ICON_STOP	 = 0x200;	// should look like  []
    const ICON_PAUSE = 0x201;	// should look like  ||
    const ICON_PLAY  = 0x202;	// should look like  >
    const ICON_PLAYR = 0x203;	// should llok like  <
    const ICON_FF    = 0x204;	// should look like  >>
    const ICON_FR	   = 0x205;	// should look like  <<
    const ICON_NEXT	 = 0x206;	// should look like  >|
    const ICON_PREV	 = 0x207;	// should look like  |<
    const ICON_REC   = 0x208;	// should look like  ()

    public $displayProps;

    protected $loadedDrivers = array();
    protected $container;


    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Load driver based on no logic at all :)
     * @param name  Driver section name.
     * @retval  <0  error.
     * @retval   0  OK, driver is an input driver only.
     * @retval   1  OK, driver is an output driver.
     * @retval   2  OK, driver is an output driver that needs to run in the foreground.
     */
    public function loadDriver($name = 'piplate')
    {
        // Kinda just gonna have the one driver
        // so don't bother with logic...
        $driver = new Piplate($this->container);
        $this->loadedDrivers[] = $driver;

        // if driver does output
        if ($driver->doesOutput() && empty($this->displayProps)) {
            $this->displayProps = new \stdClass();
            $this->displayProps->width = $driver->width();
            $this->displayProps->height = $driver->height();
            $this->displayProps->cellWidth = $driver->cellWidth();
            $this->displayProps->cellHeight = $driver->cellHeight();
        }

        return 1;
    }

    /**
     * Unload all loaded drivers.
     */
    public function unloadAll()
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'close')) {
                $driver->close();
            }
        }
    }

    /**
     * Get information from loaded drivers.
     * @return  string
     */
    public function getInfo()
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'getInfo')) {
                $driver->getInfo();
            }
        }
    }

    /**
     * Clear screen on all loaded drivers.
     * Call clear() function of all loaded drivers that have a clear() function defined.
     */
    public function clear()
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'clear')) {
                $driver->clear();
            }
        }
    }

    /**
     * Flush data on all loaded drivers to LCDs.
     * Call flush() function of all loaded drivers that have a flush() function defined.
     */
    public function flush()
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'flush')) {
                $driver->flush();
            }
        }
    }

    /**
     * Write string to all loaded drivers.
     * Call string() function of all loaded drivers that have a string() function defined.
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param string   String that gets written.
     */
    public function string($x, $y, $string)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'string')) {
                $driver->string($x, $y, $string);
            }
        }
    }

    /**
     * Write character to all loaded drivers.
     * Call chr() function of all loaded drivers that have a chr() function defined.
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param chr   String that gets written.
     */
    public function chr($x, $y, $chr)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'chr')) {
                $driver->chr($x, $y, $chr);
            }
        }
    }

    public function vbar($x, $y, $len, $promille, $pattern)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'vbar')) {
                $driver->vbar($x, $y, $len, $promille, $pattern);
            }
        }
    }

    public function hbar($x, $y, $len, $promille, $pattern)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'hbar')) {
                $driver->hbar($x, $y, $len, $promille, $pattern);
            }
        }
    }

    /**
     * Write a big number to all output drivers.
     * For drivers that define a num() function, call it.
     * @param x        Horizontal character position (column).
     * @param num      Character to write (0 - 10 with 10 representing ':')
     */
    public function num($x, $num)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'num')) {
                $driver->num($x, $num);
            }
        }
    }

    /**
     * Perform heartbeat on all drivers.
     * For drivers that define a heartbeat() function, call it;
     * otherwise call the general driver_alt_heartbeat() function from the server core.
     * @param state    Heartbeat state.
     */
    public function heartbeat($state)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'heartbeat')) {
                $driver->heartbeat($state);
            }
        }
    }

    /**
     * Write icon to all drivers.
     * For drivers that define a icon() function, call it.
     * @param x        Horizontal character position (column).
     * @param y        Vertical character position (row).
     * @param icon     synbolic value representing the icon.
     */
    public function icon($x, $y, $icon)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'icon')) {
                $driver->icon($x, $y, $icon);
            }
        }
    }

    public function cursor($x, $y, $state)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'cursor')) {
                $driver->cursor($x, $y, $state);
            }
        }
    }

    /**
     * Set backlight on all drivers.
     * Call backlight() function of all drivers that have a backlight() function defined.
     * @param state    New backlight status.
     */
    public function backlight($state)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'backlight')) {
                $driver->backlight($state);
            }
        }
    }

    /**
     * Set output on all drivers.
     * Call ouptput() function of all drivers that have an ouptput() function defined.
     * @param state    New ouptut status.
     */
    public function output($state)
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'ouptput')) {
                $driver->ouptput($state);
            }
        }
    }

    /**
     * Get key presses from loaded drivers.
     * @return  Pointer to key string for first driver ithat has a getKey() function defined
     *          and for which the getKey() function returns a key; otherwise  null.
     */
    public function getKey()
    {
        foreach ($this->loadedDrivers as $driver) {
            if (method_exists($driver, 'getKey')) {
                $keystroke = $driver->getKey();
                if ($keystroke != null) {
                    return $keystroke;
                }
            }
        }
    }
}
