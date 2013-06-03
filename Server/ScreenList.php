<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Server;
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
    protected $autorotate = true;


    public function __construct($container)
    {
        $this->container = $container;

    }

    /**
     * Shuts down the screenlist.
     */
    public function shutdown()
    {

    }

    /**
     * Adds a screen to the screenlist.
     */
    public function add($screen)
    {
        $this->screenList[$screen->id] = $screen;
    }

    /**
     * Removes a screen from the screenlist.
     */
    public function remove($screen)
    {
        $this->container->log(LOG_DEBUG, 'removeScreen:' . $screen->id);
        unset($this->screenList[$screen->id]);

        $current = $this->current();

        if (($current instanceof Screen) && ($current->id == $screen->id)) {
            $this->gotoNext();
            $current = $this->current();
            if (($current instanceof Screen) && ($current->id == $screen->id)) {
                // Hmm, no other screen had same priority
                unset($this->screenList[$screen->id]);

                // And now once more
                $this->gotoNext();

            }
        }

    }

    /**
     * Processes the screenlist.
     * Decides if we need to switch to an other screen.
     */
    public function process()
    {
        // Sort the list according to priority class
        uasort($this->screenList, array($this, "comparePriority"));
        reset($this->screenList);
        $f = current($this->screenList);

        // Check whether there is an active screen
        $s = $this->current();
        if (empty($s)) {
            // We have no active screen yet.
            // Try to switch to the first screen in the list...
            $s = $f;
            if (empty($s)) {
                // There was no screen in the list
                return;
            }
            $this->switchScreen($s);
            return;
        } else {
            // There already was an active screen.
            // Check to see if it has an expiry time. If so, decrease it
            // and then check to see if it has expired.
            // Remove the screen if expired.

            if ($s->timeout != -1) {
                --$s->timeout;
                $this->container->log(LOG_DEBUG, "Active screen [$s->id] has $s->timeout");
                if ($s->timeout <= 0) {
                    // Expired, we can destroy it
                    $this->container->log(LOG_DEBUG, "Removing expired screen [$s->id]");
                    $s->destroy();
                }
            }


        }

        // OK, current situation examined. We can now see if we need to switch.
        // Is there a screen of a higher priority class than the current one ?
        if ($f->priority > $s->priority) {
            // Yes, switch to that screen, job done
            $this->container->log(LOG_DEBUG, "High priority screen [$f->id] selected");
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
    public function switchScreen(Screen $s)
    {
        $current = $this->current();
        if (($current instanceof Screen) && ($current->id == $s->id)) {
            // Nothing to be done
            return;
        }

        if (isset($current)) {
            $c = $current->client;

            if ($c) {
                // Tell the client we're not listening any more...
                $this->container->log(LOG_DEBUG, 'ignore: ' . $current->id);
                $str = 'ignore ' . $current->id . "\n";
                $c->sendString($str);
            } else {
                // It's a server screen, no need to inform it.
            }
        }

        $c = $s->client;
        if (isset($c)) {
            $this->container->log(LOG_DEBUG, "listen [$s->id]");
            // Tell the client we're paying attention...
            $str = 'listen ' . $s->id . "\n";
            $c->sendString($str);
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
    public function current()
    {
        if (empty($this->currentId)) {
            return null;
        }

        if (isset($this->screenList[$this->currentId])) {
            return $this->screenList[$this->currentId];
        } else {
            unset($this->currentId);
        }

        return null;
    }

    /**
     * Moves on to the next screen.
     */
    public function gotoNext()
    {
        // Find current screen in screenlist
        $current = $this->current();

        $next = false;
        foreach ($this->screenList as $id => $screen) {
            if ($next) {
                $this->switchScreen($screen);

                return;
            }
            if ($current && $id == $current->id) {
                $next = true;
            }
        }

        // not found one, use the first
        reset($this->screenList);
        $screen = current($this->screenList);
        $this->switchScreen($screen);
    }

    /**
     * Moves on to the previous screen.
     */
    public function gotoPrev()
    {

    }

    /**
     * Internal function for sorting.
     *
     * @param Screen $one
     * @param Screen $two
     * @return number
     */
    public static function comparePriority($one, $two)
    {
        if (!$one) {
             return 0;
        }
        if (!$two) {
            return 0;
        }

        return ($two->priority - $one->priority);
    }
}
