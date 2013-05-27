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

class ScreenCommands
{

  protected $client;

  public function __construct($client) {
    $this->client = $client;
  }


  /**
   * Tells the server the client has another screen to offer
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
   * The client requests that the server forget about a screen
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
   * Configures info about a particular screen, such as its
   *  name, priority, or duration
   *
   *
   * Usage: screen_set <id> [-name <name>] [-wid <width>] [-hgt <height>]
   *     [-priority <prio>] [-duration <int>] [-timeout <int>]
   *     [-heartbeat <type>] [-backlight <type>]
   *     [-cursor <type>] [-cursor_x <xpos>] [-cursor_y <ypos>]
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

  /**
   * Tells the server the client would like to accept keypresses
   * of a particular type when the given screen is active on the display
   *
   * Usage: screen_add_key <screenid> <keylist>
   */
  public function addKey($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    //TODO: functionality

    Server::sendString($this->client->stream, "success\n");

    return 0;
  }

  /**
   * Tells the server the client would NOT like to accept keypresses
   * of a particular type when the given screen is active on the display
   *
   * Usage: screen_del_key <screenid> <keylist>
   */
  public function delKey($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    //TODO: functionality

    Server::sendString($this->client->stream, "success\n");

    return 0;
  }

}
