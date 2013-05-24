<?php
namespace Theapi\Lcdproc\Server\Commands;


use Theapi\Lcdproc\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

class ClientCommands
{

  protected $client;

  public function __construct($client) {
    $this->client = $client;
  }
  /**
	 * The client must say "hello" before doing anything else.
	 *
   * Usage: hello
	 */
  public function hello($args) {
    $this->client->setStateActive();

    // Should really ask the driver for its dimensions
    Server::sendString($this->client->stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
  }

  /**
	 * Sets info about the client, such as its name
	 *
   * Usage: client_set -name <id>
	 */
  public function clientSet($args) {

    if (count($args) < 2) {
      throw new ClientException($this->client->stream, 'not enough arguments');
    }

    $key = trim($args[0], ' -');
    $value = trim($args[1]);

    if (!empty($key) && !empty($value)) {
      if ($key != 'name') {
        throw new ClientException($this->client->stream, "invalid parameter ($key)");
      }

      $this->name = $value;
      Server::sendString($this->client->stream, "success\n");
    }

  }
}