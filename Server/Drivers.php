<?php
namespace Theapi\Lcdproc\Server;

/**
 * Manage the lists of loaded drivers and perform actions on all drivers.
 */

use Theapi\Lcdproc\Server;

class Drivers
{

  protected $loadedDrivers = array();
  protected $displayProps = array();

  public function loadDriver($name) {

  }

  /**
   * Unload all loaded drivers.
   */
  public function unloadAll() {

  }

  /**
   * Get information from loaded drivers.
   * @return  Pointer to information string of first driver with get_info() function defined,
   *          or the empty string if no driver has a get_info() function.
   */
  public function getInfo() {

  }

  /**
   * Clear screen on all loaded drivers.
   * Call clear() function of all loaded drivers that have a clear() function defined.
   */
  public function clear() {

  }

  /**
   * Flush data on all loaded drivers to LCDs.
   * Call flush() function of all loaded drivers that have a flush() function defined.
   */
  public function flush() {

  }

  /**
   * Write string to all loaded drivers.
   * Call string() function of all loaded drivers that have a flush() function defined.
   * @param x        Horizontal character position (column).
   * @param y        Vertical character position (row).
   * @param string   String that gets written.
   */
  public function string(int $x, int $y, $string) {

  }

}
