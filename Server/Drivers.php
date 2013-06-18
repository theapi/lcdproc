<?php
namespace Theapi\Lcdproc\Server;

/**
 * Manage the lists of loaded drivers and perform actions on all drivers.
 */


class Drivers
{

    public $displayProps;

    protected $loadedDrivers = array();
    protected $container;


    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Load driver
     * @param name  Driver class name.
     * @retval  <0  error.
     * @retval   0  OK, driver is an input driver only.
     * @retval   1  OK, driver is an output driver.
     * @retval   2  OK, driver is an output driver that needs to run in the foreground.
     */
    public function loadDriver($name = 'Piplate')
    {

        // load the driver, add the namespace so it can be found
        $fullClass = 'Theapi\Lcdproc\Server\Drivers\\' . $name;
        if (!class_exists($fullClass)) {
            throw new \Exception('Undefined driver: ' . $name);
        }

        $driver = new $fullClass($this->container);

        // Add driver to list
        $this->loadedDrivers[] = $driver;

        // If first driver, store display properties
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
                return $driver->getInfo();
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

    /**
     * Tells the unconnected drivers try to reconnect.
     *
     * Not part of lcdproc spec
     */
    public function connect()
    {
        foreach ($this->loadedDrivers as $driver) { var_dump($driver->getInfo());
            if (method_exists($driver, 'connect')) {
                try {
                    $driver->connect();
                } catch (\Exception $e) {}
            }
        }
    }
}
