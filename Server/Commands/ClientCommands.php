<?php
namespace Theapi\Lcdproc\Server\Commands;


use Theapi\Lcdproc\Server;

class ClientCommands
{

  protected $client;

  // Map lcdproc commands to methods
  protected $commands = array(
    'hello' => 'hello',
    'client_set' => 'clientSet',
  );

  public function __construct($client) {
    $this->client = $client;
  }

  public function getCommands() {
    return $this->commands;
  }

  /**
	 * The client must say "hello" before doing anything else.
	 *
   * Usage: hello
	 */
  public function hello($args) {
    $this->client->setStateActive();
    Server::sendString($this->client->stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
  }

  /**
	 * Sets info about the client, such as its name
	 *
   * Usage: client_set -name <id>
	 */
  public function clientSet($args) {

    if (count($args) < 2) {
      // error
      return;
    }

    $key = trim($args[0], ' -');
    $value = trim($args[1]);

    if (!empty($key) && !empty($value)) {
      if ($key != 'name') {
        Server::sendError($this->client->stream, "invalid parameter ($key)\n");
        return;
      }
      $this->name = $value;
      Server::sendString($this->client->stream, "success\n");
    }

  }
}