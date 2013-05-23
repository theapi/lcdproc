<?php
namespace Theapi\Lcdproc\Server\Drivers;

class Piplate
{

  protected $width = 16;
  protected $height = 2;

  /**
   * Initialize the driver.
   */
  public function __construct() {
    // connect to the socket that the python script is listening on
echo "Piplate construct\n";

  }

  /**
	 * Close the driver (do necessary clean-up).
	 */
  public function close() {
    // close socket to python script
echo "Piplate close\n";

  }

  public function width() {
    return $this->width;
  }

  public function height() {
    return $this->height;
  }

  /**
   * Clear the screen.
   */
  public function clear() {

  }

  /**
   * Flush data on screen to the display.
   */
  public function flush() {

  }

  /**
   * Print a string on the screen at position (x,y).
   *
   * @param x        Horizontal character position (column).
   * @param y        Vertical character position (row).
   * @param string   String that gets written.
   */
  public function string(int $x, int $y, $string) {

  }

  /**
   * Print a character on the screen at position (x,y).
   *
   * @param x        Horizontal character position (column).
   * @param y        Vertical character position (row).
   * @param chr   String that gets written.
   */
  public function chr(int $x, int $y, $chr) {

  }

  public function vbar(int $x, int $y, int $len, int $promille, int $pattern) {

  }

  public function hbar(int $x, int $y, int $len, int $promille, int $pattern) {

  }

  /**
	 * Write a big number.
	 * @param x        Horizontal character position (column).
	 * @param num      Character to write (0 - 10 with 10 representing ':')
	 */
  public function num(int $x, int $num) {

  }

  /**
	 * Perform heartbeat.
	 * @param state    Heartbeat state.
	 */
  public function heartbeat(int $state) {

  }

  /**
	 * Write icon.
	 * @param x        Horizontal character position (column).
	 * @param y        Vertical character position (row).
	 * @param icon     synbolic value representing the icon.
	 */
  public function icon(int $x, int $y, int $icon) {

  }

  public function cursor(int $x, int $y, int $state) {

  }

  /**
	 * Turn the display backlight on or off.
	 *
	 * @param state    New backlight status.
	 */
  public function backlight(int $state) {

  }

  /**
	 * Get key presses.
	 */
  public function getKey() {

  }

  /**
   * Provide some information about this driver.
   */
  public function getInfo() {
    return 'Adafruit pilate driver';
  }

}
