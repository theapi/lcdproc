<?php
namespace Theapi\Lcdproc\Server;

/**
 * All actions that can be performed on the list of screens.
 * This file also manages the rotation of screens.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class ScreenList
{

  protected $screenList = array();

  /**
   * If enabled, screens will rotate
   */
  protected $autorotate = TRUE;

  /**
   * Shuts down the screenlist.
   */
  public function shutdown() {

  }

  /**
   * Adds a screen to the screenlist.
   */
  public function add($screen) {
    $this->screenList[$screen->id] = $screen;
  }

  /**
   * Removes a screen from the screenlist.
   */
  public function remove() {

  }

  /**
   * Processes the screenlist.
   * Decides if we need to switch to an other screen.
   */
  public function process() {

  }

  /**
   * Switches to an other screen in the proper way.
   * Informs clients of the switch.
	 * ALWAYS USE THIS FUNCTION TO SWITCH SCREENS.
	 *
   * NB: cannot use the method name "switch" as used in the original source
   */
  public function switchScreen() {

  }

  /**
   * Returns the currently active screen.
   */
  public function current() {

  }

  /**
   * Moves on to the next screen.
   */
  public function gotoNext() {

  }

  /**
   * Moves on to the previous screen.
   */
  public function gotoPrev() {

  }

  public function comparePriority() {

  }

}