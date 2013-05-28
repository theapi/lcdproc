<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server;
use Theapi\Lcdproc\Server\Screen;

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
  protected $currentScreenStartTime = 0;
  protected $currentId;

  /**
   * If enabled, screens will rotate
   */
  protected $autorotate = TRUE;


  public function __construct($container) {
    $this->container = $container;

  }

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

    if (empty($this->currentId)) {
      $this->currentId = $screen->id;
    }
  }

  /**
   * Removes a screen from the screenlist.
   */
  public function remove($screen) {
    $current_screen = $this->current();

    if ($screen == $current_screen) {
      $this->gotoNext();
      if ($screen == $current_screen) {
        // Hmm, no other screen had same priority
        unset($this->screenList[$screen->id]);

        // And now once more
        $this->gotoNext();

      }
    }
    else {
      unset($this->screenList[$screen->id]);
    }
  }

  /**
   * Processes the screenlist.
   * Decides if we need to switch to an other screen.
   */
  public function process() {
    // Sort the list according to priority class
    uasort($this->screenList, array($this, "comparePriority"));
    reset($this->screenList);
    $f = current($this->screenList);

    $s = $this->current();
    if (empty($s)) {
      // nothing to do
      return;
    }

    // There already was an active screen.
		// Check to see if it has an expiry time. If so, decrease it
		// and then check to see if it has expired.
		// Remove the screen  if expired.
		// TODO ...


    // OK, current situation examined. We can now see if we need to switch.
    // Is there a screen of a higher priority class than the current one ?
    if ($f->priority > $s->priority) {
      // Yes, switch to that screen, job done
      $this->switchScreen($f);
    }

    // Current screen has been visible long enough and is it of 'normal' priority ?
    if ($this->autorotate
      && ($this->container->timer - $this->currentScreenStartTime >= $s->duration)
  	  && $s->priority > Screen::PRI_BACKGROUND && $s->priority <= Screen::PRI_FOREGROUND) {
  		// Ah, rotate!
  		$this->gotoNext();
  	}
  }

  /**
   * Switches to an other screen in the proper way.
   * Informs clients of the switch.
	 * ALWAYS USE THIS FUNCTION TO SWITCH SCREENS.
	 *
   * NB: cannot use the method name "switch" as used in the original source
   */
  public function switchScreen(Screen $s) {

    $current = $this->current();
    if ($current == $s) {
      // Nothing to be done
      return;
    }

    if (isset($current)) {
      $c = $current->client;
      if (isset($c)) {
        // Tell the client we're not listening any more...
        $str = 'ignore ' . $current->id . "\n";
        Server::sendString($c->stream, $str);
      } else {
        // It's a server screen, no need to inform it.
      }
    }

    $c = $s->client;
    if (isset($c)) {
      // Tell the client we're paying attention...
      $str = 'listen ' . $s->id . "\n";
      Server::sendString($c->stream, $str);
    } else {
      // It's a server screen, no need to inform it.
    }

    $this->currentId = $s->id;

    // timer is th global from main.c (Server.php)
    $this->currentScreenStartTime = $this->container->timer;
  }

  /**
   * Returns the currently active screen.
   */
  public function current() {
    if (empty($this->currentId)) {
       return NULL;
    }

    if (isset($this->screenList[$this->currentId])) {
      return $this->screenList[$this->currentId];
    }
    else {
      unset($this->currentId);
    }

    return NULL;
  }

  /**
   * Moves on to the next screen.
   */
  public function gotoNext() {
    // Find current screen in screenlist
    $current = $this->current();

    $next = FALSE;
    foreach ($this->screenList as $id => $screen) {
      if ($next) {
        $this->switchScreen($screen);
        return;
      }
      if ($id == $current->id) {
        $next = TRUE;
      }
    }
  }

  /**
   * Moves on to the previous screen.
   */
  public function gotoPrev() {

  }

  /**
   * Internal function for sorting.
   *
   * @param Screen $one
   * @param Screen $two
   * @return number
   */
  public static function comparePriority($one, $two) {
    if (!$one) return 0;
    if (!$two) return 0;

    return ($two->priority - $one->priority);
  }

}