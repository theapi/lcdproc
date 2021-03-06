<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Server;
use Theapi\Lcdproc\Server\Screen;
use Theapi\Lcdproc\Server\Render;
use Theapi\Lcdproc\Server\Widget;
use Theapi\Lcdproc\Server\Clients;
use Theapi\Lcdproc\Server\Drivers;

/**
 * This contains code to allow the server to generate its own screens.
 * Currently, the startup, goodbye and server status screen are provided. The
 * server status screen shows total number of connected clients, and the
 * combined total of screens they provide.
 *
 * It is interesting to note that the server creates a special screen
 * definition for its screens, but uses the same widget set made available
 * to clients.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class ServerScreens
{

    const SERVERSCREEN_OFF   = 0;
    const SERVERSCREEN_ON    = 1;
    const SERVERSCREEN_BLANK = 2;

    // not part of spec
    // show server screen only when no clients
    const SERVERSCREEN_SOLO = 4;

    protected $container;

    protected $rotateServerScreen;


    public function __construct($container)
    {
        $this->container = $container;

        $this->rotateServerScreen = $this->container->config->serverScreen;
        if ($this->rotateServerScreen == self::SERVERSCREEN_OFF) {
            // no server screen thanks
            return;
        }

        $hasHelloMsg = empty($this->container->config->helloMsg) ? false : true;

        // Create the screen
        $this->screen = new Screen($this->container, "_server_screen", null);
        $this->screen->name = "Server screen";
        $this->screen->duration = Server::RENDER_FREQ;
        $this->screen->heartbeat = Render::HEARTBEAT_ON;

        // Create all the widgets...
        for ($i = 0; $i < $this->container->drivers->displayProps->height; $i++) {
            $id = 'line' . ($i+1);

            try {
                $w = new Widget($id, Widget::WID_STRING, $this->screen);
            } catch (ClientException $e) {
                echo $e->getMessage();
                // TODO: log rather than echo
                return;
            }

            $this->screen->addWidget($w);
            $w->x = 1;
            $w->y = $i+1;
        }

        // set parameters for server_screen and its widgets
        $this->reset($this->rotateServerScreen, !$hasHelloMsg, !$hasHelloMsg);

        // set the widgets depending on the Hello option in config
        if ($hasHelloMsg) {
            for ($i = 0; $i < $this->container->drivers->displayProps->height; $i++) {
                $id = 'line' . $i+1;
                $w = $this->screen->findWidget($id);
                if ($w) {
                    $w->text = $this->container->config->helloMsg[$i];
                }
            }
        }

        // And enqueue the screen
        $this->container->screenList->add($this->screen);
    }

    public function reset($rotate, $heartbeat, $title)
    {
        // naughty hard coded for now
        $w = $this->screen->findWidget('line1');
        if ($w) {
            $w->text = 'PHP LCDproc';
            $w->x = 3;
        }

        /*
         server_screen->heartbeat = (heartbeat && (rotate != SERVERSCREEN_BLANK))
        ? HEARTBEAT_OPEN : HEARTBEAT_OFF;
        server_screen->priority = (rotate == SERVERSCREEN_ON)
        ? PRI_INFO : PRI_BACKGROUND;
        */

        /*
         for ($i = 0; $i < $this->drivers->displayProps->height; $i++) {
        $id = 'line' . ($i+1);
        $w = $this->screen->findWidget($id);
        if ($w) {
        $w->x = 1;
        $w->y = $i+1;
        $w->type = (($i == 0) && ($title) && ($rotate != self::SERVERSCREEN_BLANK))
        ? Widget::WID_TITLE : Widget::WID_STRING;

        if ($w->text != null) {
  		    if (($i == 0) && ($title) && ($rotate != self::SERVERSCREEN_BLANK)) {
        $w->text = 'PHP LCDproc Server';
  		    }
        }

        }
        }
        */

    }

    public function update()
    {
        // update statistics if we do not only want to show a blank screen
        if ($this->rotateServerScreen != self::SERVERSCREEN_BLANK) {
            $w = $this->screen->findWidget('line2');
            //$w->text = (string) microtime(true);
            $w->text = date('Y/m/d H:i');
        }

    }
}
