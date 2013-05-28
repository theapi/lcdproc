<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Commands\ClientCommands;
use Theapi\Lcdproc\Server\Commands\ServerCommands;
use Theapi\Lcdproc\Server\Commands\ScreenCommands;
use Theapi\Lcdproc\Server\Commands\WidgetCommands;
use Theapi\Lcdproc\Server;

class Client
{

  public $stream;
  protected $messages = array();
  public $backlight;
  protected $heartbeat;
  protected $state;
  protected $name;
  protected $menu;
  protected $screenlist = array();

  // Set the mapping of lcdproc commands to our methods
  protected $commands = array(
    'hello'          => array('clientCommands', 'hello'),
    'client_set'     => array('clientCommands', 'clientSet'),

    'output'         => array('serverCommands', 'output'),
    'sleep'          => array('serverCommands', 'sleep'),
    'noop'           => array('serverCommands', 'noop'),

    'screen_add'     => array('screenCommands', 'add'),
    'screen_del'     => array('screenCommands', 'del'),
    'screen_set'     => array('screenCommands', 'set'),
    'screen_add_key' => array('screenCommands', 'addKey'),
    'screen_del_key' => array('screenCommands', 'delKey'),

    'widget_add'     => array('widgetCommands', 'add'),
    'widget_del'     => array('widgetCommands', 'del'),
    'widget_set'     => array('widgetCommands', 'set'),
  );

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

  public function __construct($container, $stream) {
    $this->container = $container;
    $this->stream = $stream;
    $this->state = self::STATE_NEW;

    $this->clientCommands = new ClientCommands($this);
    $this->serverCommands = new ServerCommands($this);
    $this->screenCommands = new ScreenCommands($this);
    $this->widgetCommands = new ScreenCommands($this);
  }

  public function command($name, $args) {

    // Got to say hello first
    if (!$this->isActive() && $name != 'hello') {
      throw new ClientException($this->stream, 'Invalid command ' . $name);
    }

    if (isset($this->commands[$name])) {
      $commandHandler = $this->commands[$name][0];
      $method = $this->commands[$name][1];
      if (method_exists($this->$commandHandler, $method)) {
        $error = $this->$commandHandler->$method($args);
        if ($error) {
          throw new ClientException($this->stream, 'Function returned error ' . $method);
        }
      }
      else {
        // oops there's a command mapping mixup
        // TODO: exceptions for coding errors
      }
    }
    else {
      throw new ClientException($this->stream, 'Invalid command ' . $name);
    }
  }

  public function getStream() {
    return $this->stream;
  }

  public function setStateActive() {
    $this->state = self::STATE_ACTIVE;
  }

  public function setStateGone() {
    $this->state = self::STATE_GONE;
  }

  public function isActive() {
    if ($this->state == self::STATE_ACTIVE) {
      return TRUE;
    }
    return FALSE;
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