<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Commands\ClientCommands;

use Theapi\Lcdproc\Server;

class Client
{

  public $stream;
  protected $messages = array();
  protected $backlight;
  protected $heartbeat;
  protected $state;
  protected $name;
  protected $menu;
  protected $screenlist = array();
  protected $commands;

	/**
	 * Client did not yet send hello.
	 */
  const STATE_NEW = 0;
	/**
	 * Client sent hello, but not yet bye.
	 */
  const STATE_ACTIVE = 1;
  /**
	 * Client sent bye.
	 */
  const STATE_GONE = 2;

  public function __construct($stream) {
    $this->create($stream);
  }

  public function command($name, $args) {

    // Got to say hello first
    if (!$this->isActive() && $name != 'hello') {
      // TODO check that even huh should be sent if not helloed
      Server::sendError($this->stream, "\n");
      return;
    }

    // Get the mapping of lcdproc commands to our methods
    $commands = $this->commands->getCommands();
    if (isset($commands[$name]) && method_exists($this->commands, $commands[$name])) {
      $method = $commands[$name];
      $this->commands->$method($args);
    }
    else {
      Server::sendError($this->stream, "unkown command\n");
    }
  }

  public function getStream() {
    return $this->stream;
  }

  public function setStateActive() {
    $this->state = self::STATE_ACTIVE;
  }

  public function isActive() {
    if ($this->state == self::STATE_ACTIVE) {
      return TRUE;
    }
    return FALSE;
  }

  protected function create($stream) {
    $this->stream = $stream;
    $this->state = self::STATE_NEW;

    $this->commands = new ClientCommands($this);
  }

  public function destroy() {

  }

  /**
   * Add and remove messages from the client's queue...
   */
  public function addMessage($message) {

  }

  /**
   * Woo-hoo!  A simple function.  :)
   */
  public function getMessage() {

  }

  public function findScreen($id) {

  }

  public function addScreen($screen) {

  }

  public function removeScreen($screen) {

  }

  public function sceenCount() {

  }



}