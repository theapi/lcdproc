<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Client;
use Theapi\Lcdproc\Server\Config;

/**
 * This stores all the screen definition-handling code. Functions here
 * provide means to create new screens and destroy existing ones. Screens are
 * identified by client and by the client's own identifiers for screens.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

class Screen
{

  protected $config;

  public $name = NULL;
  public $duration = 0;


	//protected $priority = PRI_INFO; //TODO: PRI_INFO
	protected $backlight = Config::BACKLIGHT_OFF;
	protected $heartbeat = Config::HEARTBEAT_OFF;
	protected $width;
	protected $height;
	protected $keys = NULL;
	protected $client = NULL;
	protected $widgetlist = array();
	protected $timeout = -1; 	/*ignored unless greater than 0.*/
	protected $cursor = Config::CURSOR_OFF;
	protected $cursor_x = 1;
	protected $cursor_y = 1;


	/**
   * Create a screen.
   *
   * @param Config $config
   * @param string $id
   * @param Client $client
   */
  public function __construct($config, $id, Client $client = NULL) {
    $this->config = $config;

    $this->client = $client;
    if (!$id) {
  		throw new ClientException($this->client->stream, 'Need id string');
  	}

  	$this->id = $id;
  	$this->width = $this->config->displayProps->width;
  	$this->height = $this->config->displayProps->height;

  	// Client can be NULL for serverscreens and other client-less screens

  	// menuscreen_add_screen(s)
  }

  /**
   * Destroy a screen.
   */
  public function destroy() {

  }

  /**
   * Add a widget to a screen.
   */
  public function addWidget($widget) {
    $this->widgetlist[$widget->id] = $widget;
  }

  /**
   * Remove a widget from a screen.
   */
  public function removeWidget($widget) {

  }

  /**
   * Find a widget on a screen by its id.
   */
  public function findWidget($id) {
    if (isset($this->widgetlist[$id])) {
      // not doing 'Search subscreens recursively' for now
      return $this->widgetlist[$id];
    }
    return NULL;
  }

  /**
   * Convert a priority name to the priority id.
   * @param priname  Name of the screen priority.
   */
  public function priNameToPri($priName) {

  }

  /**
   * Convert a priority id to the associated name.
   * @param pri  Priority id.
   */
  public function priToPriName($pri) {

  }
}
