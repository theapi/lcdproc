<?php
namespace Theapi\Lcdproc\Server\Commands;

/**
 * Implements handlers for the client commands concerning screens.
 *
 * This contains definitions for all the functions which clients can run.
 *
 * The client's available function set is defined here, as is the syntax
 * for each command.
 */

/* This file is part of phpLCDd, the php lcdproc server.
 *
 * This file is released under the GNU General Public License.
 * Refer to the COPYING file distributed with this package.
 *
 */

use Theapi\Lcdproc\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

class WidgetCommands
{

  protected $client;

  public function __construct($client) {
    $this->client = $client;
  }


  /**
   * Adds a widget to a screen, but doesn't give it a value
   *
   * Usage: widget_add <screenid> <widgetid> <widgettype> [-in <id>]
   */
  public function add($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    //TODO: functionality

    Server::sendString($this->client->stream, "success\n");

    return 0;
  }


  /**
   * Removes a widget from a screen, and forgets about it
   *
   * Usage: widget_del <screenid> <widgetid>
   */
  public function del($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    //TODO: functionality

    Server::sendString($this->client->stream, "success\n");

    return 0;
  }

  /**
   * Configures information about a widget, such as its size, shape,
   * contents, position, speed, etc.
   *
   *
   * widget_set <screenid> <widgetid> <widget-SPECIFIC-data>
   *
   */
  public function set($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    //TODO: functionality

    Server::sendString($this->client->stream, "success\n");

    return 0;
  }

}
