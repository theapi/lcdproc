<?php
namespace Theapi\Lcdproc\Server\Commands;


use Theapi\Lcdproc\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

class ServerCommands
{

  protected $client;

  public function __construct($client) {
    $this->client = $client;
  }

  /**
   * Sets the state of the output port
   *
   * (so does nothing)
   */
  public function output($args) {
    if (!$this->client->isActive()) {
      throw new ClientException($this->client->stream);
    }

    // Lie :)
    Server::sendString($this->client->stream, "success\n");
  }

  /**
	 * The sleep_func was intended to make the server sleep for some seconds.
   * This function is currently ignored as making the server sleep actually
   * stalls it and disrupts other clients.
	 */
  public function sleep($args) {
    if (!$this->client->isActive()) {
      throw new ClientException($this->client->stream);
    }

    Server::sendString($this->client->stream, "ignored (not fully implemented)\n");
  }

  /**
	 * Does nothing, returns "noop complete" message.
	 */
  public function noop($args) {
    if (!$this->client->isActive()) {
      throw new ClientException($this->client->stream);
    }

    Server::sendString($this->client->stream, "noop complete\n");
  }

}
