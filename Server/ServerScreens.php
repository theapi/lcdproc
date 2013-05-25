<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Screen;
use Theapi\Lcdproc\Server\Widget;

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

  protected $container;


  public function __construct($container) {
    $this->container = $container;
    $this->config = $this->container->config;

    $hasHelloMsg = empty($this->config->helloMsg) ? FALSE : TRUE;

    // Create the screen
    $this->screen = new Screen($this->config, "_server_screen", NULL);
    $this->screen->name = "Server screen";
	  $this->screen->duration = 1;

	  // Create all the widgets...
    for ($i = 0; $i < $this->config->displayProps->height; $i++) {
      $id = 'line' . $i+1;

      try {
  		  $w = new Widget($id, Widget::WID_STRING, $this->screen);
      } catch (CLientException $e) {
        echo $e->getMessage();
        return;
      }


  		$this->screen->addWidget($w);
  		$w->x = 1;
  		$w->y = $i+1;
  	}

    // set parameters for server_screen and its widgets
    //TODO: auto rotate optional
    $this->reset(Config::AUTOROTATE_ON, !$hasHelloMsg, !$hasHelloMsg);

    // set the widgets depending on the Hello option in config
    if ($hasHelloMsg) {
      for ($i = 0; $i < $this->config->displayProps->height; $i++) {
        $id = 'line' . $i+1;
        $w = $this->screen->findWidget($id);
        if ($w) {
          $w->text = $this->config->helloMsg;
        }
      }
    }

    // And enqueue the screen
    $this->container->screenList->add($this->screen);

  }

  public function reset($rotate, $heartbeat, $title) {
    /*
    server_screen->heartbeat = (heartbeat && (rotate != SERVERSCREEN_BLANK))
					? HEARTBEAT_OPEN : HEARTBEAT_OFF;
	  server_screen->priority = (rotate == SERVERSCREEN_ON)
					? PRI_INFO : PRI_BACKGROUND;
		*/

    for ($i = 0; $i < $this->config->displayProps->height; $i++) {
      $id = 'line' . $i+1;
      $w = $this->screen->findWidget($id);
      if ($w) {
        $w->x = 1;
  		  $w->y = $i+1;
  		  $w->type = (($i == 0) && ($title) && ($rotate != self::SERVERSCREEN_BLANK))
					? Widget::WID_TITLE : Widget::WID_STRING;

  		  if ($w->text != NULL) {
  		    if (($i == 0) && ($title) && ($rotate != self::SERVERSCREEN_BLANK)) {
  		      $w->text = 'PHP LCDproc Server';
  		    }
  		  }

      }
    }

  }

}
