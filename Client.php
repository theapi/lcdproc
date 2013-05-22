<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server;

class Client
{

  protected $stream;
  protected $messages = array();
  protected $backlight;
  protected $heartbeat;
  protected $state;
  protected $name;
  protected $menu;
  protected $screenlist = array();

  public function __construct($stream) {
    $this->create($stream);
  }

  public function getStream() {
    return $this->stream;
  }

  protected function create($stream) {
    $this->stream = $stream;
    $this->state = 'NEW';
    return $this;
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

  /**
	 * The client must say "hello" before doing anything else.
	 *
   * Usage: hello
	 */
  public function funcHello($args) {
    $this->state = 'ACTIVE'; // TODO constants for client states
    Server::sendString($this->stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
    //fwrite($stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
  }

  /**
	 * Sets info about the client, such as its name
	 *
   * Usage: client_set -name <id>
	 */
  public function funcClientSet($args) {

    if (count($args) < 2) {
      // error
      return;
    }

    $key = trim($args[0], ' -');
    $value = trim($args[1]);

    if (!empty($key) && !empty($value)) {
      if ($key != 'name') {
        Server::sendError($this->stream, "invalid parameter ($key)\n");
        //sock_printf_error(c->sock, "invalid parameter (%s)\n", p);
        return;
      }
      $this->name = $value;
      Server::sendString($this->stream, "success\n");
    }

  }

}