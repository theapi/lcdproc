<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Drivers\Piplate;

/**
 * Manage the lists of loaded drivers and perform actions on all drivers.
 */


class Drivers
{

  protected $config;

  protected $loadedDrivers = array();
  protected $displayProps;

  public function __construct($config) {
    $this->config = $config;
  }

  /**
	 * Load driver based on no logic at all :)
	 * @param name  Driver section name.
	 * @retval  <0  error.
	 * @retval   0  OK, driver is an input driver only.
	 * @retval   1  OK, driver is an output driver.
	 * @retval   2  OK, driver is an output driver that needs to run in the foreground.
	 */
  public function loadDriver($name = 'piplate') {
    // Kinda just gonna have the one driver
    // so don't bother with logic...
    $driver = new Piplate();
    $this->loadedDrivers[] = $driver;

    // if driver does output
    if ($driver->doesOutput() && empty($this->displayProps)) {
      $this->displayProps = new \stdClass();
      $this->displayProps->width = $driver->width();
      $this->displayProps->height = $driver->height();
      $this->displayProps->cellWidth = $driver->cellWidth();
      $this->displayProps->cellHeight = $driver->cellHeight();
      $this->config->displayProps = $this->displayProps;
    }

    return 1;
  }

  /**
   * Unload all loaded drivers.
   */
  public function unloadAll() {
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
  public function getInfo() {
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
  public function clear() {
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
  public function flush() {
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
  public function string(int $x, int $y, $string) {
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
  public function chr(int $x, int $y, $chr) {
    foreach ($this->loadedDrivers as $driver) {
      if (method_exists($driver, 'chr')) {
        $driver->chr($x, $y, $chr);
      }
    }
  }

  public function vbar(int $x, int $y, int $len, int $promille, int $pattern) {

  }

  public function hbar(int $x, int $y, int $len, int $promille, int $pattern) {

  }

  /**
	 * Write a big number to all output drivers.
	 * For drivers that define a num() function, call it.
	 * @param x        Horizontal character position (column).
	 * @param num      Character to write (0 - 10 with 10 representing ':')
	 */
  public function num(int $x, int $num) {
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
  public function heartbeat(int $state) {
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
  public function icon(int $x, int $y, int $icon) {

  }

  public function cursor(int $x, int $y, int $state) {

  }

  /**
	 * Set backlight on all drivers.
	 * Call backlight() function of all drivers that have a backlight() function defined.
	 * @param state    New backlight status.
	 */
  public function backlight(int $state) {
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
  public function output(int $state) {
    foreach ($this->loadedDrivers as $driver) {
      if (method_exists($driver, 'ouptput')) {
        $driver->ouptput($state);
      }
    }
  }

  /**
	 * Get key presses from loaded drivers.
	 * @return  Pointer to key string for first driver ithat has a getKey() function defined
	 *          and for which the getKey() function returns a key; otherwise \c NULL.
	 */
  public function getKey() {
    foreach ($this->loadedDrivers as $driver) {
      if (method_exists($driver, 'getKey')) {
        $keystroke = $driver->getKey();
        if ($keystroke != NULL) {
          return $keystroke;
        }
      }
    }
  }


}
