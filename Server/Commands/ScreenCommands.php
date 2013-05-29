<?php
namespace Theapi\Lcdproc\Server\Commands;

use Theapi\Lcdproc\Server\Screen;
use Theapi\Lcdproc\Server\Config;

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

    if (count($args) != 1) {
      throw new ClientException($this->client->stream, 'Usage: screen_add <screenid>');
    }

    $s = $this->client->findScreen($args[0]);
    if ($s != NULL) {
      throw new ClientException($this->client->stream, 'Screen already exists');
    }

    $s = new Screen($this->client->config, $args[0]);
    if ($s != NULL) {
      throw new ClientException($this->client->stream, 'failed to create screen');
    }

    $err = $this->client->addScreen($s);
    if ($err == 0) {
       Server::sendString($this->client->stream, "success\n");
    }
    else {
      Server::sendString($this->client->stream, "failed to add screen\n");
    }

    return 0;
  }


  /**
   * The client requests that the server forget about a screen
   */
  public function del($args) {
    if (!$this->client->isActive()) {
      return 1;
    }

    if (count($args) != 1) {
      throw new ClientException($this->client->stream, 'Usage: screen_del <screenid>');
    }

    $s = $this->client->findScreen($args[0]);
    if ($s == NULL) {
      throw new ClientException($this->client->stream, 'Unknown screen id');
    }

    $err = $this->client->removeScreen($s);
    if ($err == 0) {
       Server::sendString($this->client->stream, "success\n");
    }
    else {
      Server::sendString($this->client->stream, "failed to remove screen\n");
    }

    $s->destroy();

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

      if (count($args) == 0) {
      throw new ClientException($this->client->stream, 'Usage: screen_set <id>
         [-name <name>]
				 [-wid <width>] [-hgt <height>] [-priority <prio>]
				 [-duration <int>] [-timeout <int>]
				 [-heartbeat <type>] [-backlight <type>]
				 [-cursor <type>]
				 [-cursor_x <xpos>] [-cursor_y <ypos>]');
    }

    if (count($args) == 1) {
      throw new ClientException($this->client->stream, 'What do you want to set?');
    }

    $s = $this->client->findScreen($args[0]);
    if ($s == NULL) {
      throw new ClientException($this->client->stream, 'Unknown screen id');
    }

    // Handle the rest of the parameters

    // ignore leading '-' in options: we allow both forms
    $key = trim($args[0], ' -');
    $value = trim($args[1]);

    switch ($key) {

      // Handle the "name" parameter
      case 'name':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-name requires a parameter');
        }
        else {
          $s->name = $value;
          Server::sendString($this->client->stream, "success\n");
        }
        break;

      // Handle the "priority" parameter
      case 'priority':
        // first try to interpret it as a number
        if (is_numeric($value)) {
          $number = (int) $value;
          if ($number <= 64) {
          	$number = Screen::PRI_FOREGROUND;
          } else if (number < 192) {
          	$number = Screen::PRI_INFO;
          } else {
          	$number = Screen::PRI_BACKGROUND;
          }
				} else {
				  // Try if it is a priority class
          $number = Screen::priNameToPri($value);

				}
				if ($number >= 0) {
				  $s->priority = $number;
				  Server::sendString($this->client->stream, "success\n");
				} else {
				  throw new ClientException($this->client->stream, '-priority requires a parameter');
				}
        break;

      // Handle the "duration" parameter
      case 'duration':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-heartbeat requires a parameter');
        }
        $s->duration = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "wid" parameter
      case 'wid':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-wid requires a parameter');
        }
        $s->width = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "hgt" parameter
      case 'hgt':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-hgt requires a parameter');
        }
        $s->height = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "timeout" parameter
      case 'timeout':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-timeout requires a parameter');
        }
        $s->timeout = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "backlight" parameter
      case 'backlight':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-backlight requires a parameter');
        }

        // set the backlight status based on what the client has set
        switch ($this->client->backlight) {
          case Config::BACKLIGHT_OPEN:
            switch ($value) {
              case 'on':
                $s->backlight = Config::BACKLIGHT_ON;
                break;

              case 'off':
                $s->backlight = Config::BACKLIGHT_OFF;
                break;

              case 'toggle':
                if ($s->backlight == Config::BACKLIGHT_ON) {
                  $s->backlight = Config::BACKLIGHT_OFF;
                } else {
                  $s->backlight = Config::BACKLIGHT_ON;
                }
                break;

              case 'blink':
                $s->backlight = Config::BACKLIGHT_BLINK;
                break;
              case 'flash':
                $s->backlight = Config::BACKLIGHT_FLASH;
                break;
              default:
                // Maybe its a colour
                $s->backlight = $value;
                break;
            }
          break;

          default:
            // If the backlight is not OPEN then inherit its state
            $s->backlight = $this->client->backlight;
            break;
        }

        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "cursor" parameter
      case 'cursor':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-cursor requires a parameter');
        }

        switch ($value) {
          case 'off':
            $s->cursor = Config::CURSOR_OFF;
            break;
          case 'on':
            $s->cursor = Config::CURSOR_ON;
            break;
          case 'under':
            $s->cursor = Config::CURSOR_UNDER;
            break;
          case 'block':
            $s->cursor = Config::CURSOR_BLOCK;
            break;
        }
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "cursor_x" parameter
      case 'cursor_x':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-cursor_x requires a parameter');
        }
        $s->cursor_x = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;

      // Handle the "cursor_y" parameter
      case 'cursor_y':
        if (empty($value)) {
          throw new ClientException($this->client->stream, '-cursor_y requires a parameter');
        }
        $s->cursor_y = (int) $value;
        Server::sendString($this->client->stream, "success\n");
        break;
    }

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
