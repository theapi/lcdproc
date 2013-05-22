<?php
namespace Theapi\Lcdproc\Server;

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
    fwrite($stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
  }

  /**
	 * Sets info about the client, such as its name
	 *
   * Usage: client_set -name <id>
	 */
  public function funcClientSet($name, $id) {

  }

}