<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Commands\ClientCommands;
use Theapi\Lcdproc\Server\Commands\ServerCommands;
use Theapi\Lcdproc\Server\Commands\ScreenCommands;
use Theapi\Lcdproc\Server\Commands\WidgetCommands;
use Theapi\Lcdproc\Server\Commands\MenuCommands;
use Theapi\Lcdproc\Server\Config;
use Theapi\Lcdproc\Server;

class Client
{

    public $stream;
    protected $messages = array();
    public $backlight = Config::BACKLIGHT_OPEN;
    protected $heartbeat;
    protected $state;
    protected $name;
    protected $menu;
    protected $screenList = array();

    // Set the mapping of lcdproc commands to our methods
    protected $commands = array(
        'test'           => array('clientCommands', 'test'),
        'hello'          => array('clientCommands', 'hello'),
        'client_set'     => array('clientCommands', 'set'),
        'client_add_key' => array('clientCommands', 'addKey'),
        'client_del_key' => array('clientCommands', 'delKey'),
        'backlight'      => array('clientCommands', 'backlight'),
        'info'           => array('clientCommands', 'info'),
        'bye'            => array('clientCommands', 'bye'),

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

        'menu_add_item'  => array('menuCommands', 'addItem'),
        'menu_del_item'  => array('menuCommands', 'delItem'),
        'menu_set_item'  => array('menuCommands', 'setItem'),
        'menu_goto'      => array('menuCommands', 'menuGoto'),
        'menu_set_main'  => array('menuCommands', 'setMain'),


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

    public function __construct($container, $stream)
    {
        $this->container = $container;
        $this->stream = $stream;
        $this->state = self::STATE_NEW;

        $this->clientCommands = new ClientCommands($this);
        $this->serverCommands = new ServerCommands($this);
        $this->screenCommands = new ScreenCommands($this);
        $this->widgetCommands = new ScreenCommands($this);
        $this->menuCommands   = new MenuCommands($this);
    }

    public function command($name, $args)
    {
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
            } else {
                // oops there's a command mapping mixup
                // TODO: exceptions for coding errors
            }
        } else {
            throw new ClientException($this->stream, 'Invalid command ' . $name);
        }
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function setStateActive()
    {
        $this->state = self::STATE_ACTIVE;
    }

    public function setStateGone()
    {
        $this->state = self::STATE_GONE;
    }

    public function isActive()
    {
        if ($this->state == self::STATE_ACTIVE) {
            return true;
        }
        return false;
    }

    public function destroy()
    {

    }

    /**
     * Add and remove messages from the client's queue...
     */
    public function addMessage($message)
    {

    }

    /**
     * Woo-hoo!  A simple function.  :)
     */
    public function getMessage()
    {

    }

    public function findScreen($id)
    {
        if (empty($id)) {
            return null;
        }

        if (isset($this->screenList[$id])) {
            return $this->screenList[$id];
        }

        return null;
    }

    public function addScreen($screen)
    {
        $this->screenList[$screen->id] = $screen;

        // Now, add it to the screenlist...
        $this->container->screenList->add($screen);

        return 0;
    }

    public function removeScreen($screen)
    {
        if (isset($this->screenList[$screen->id])) {
            unset($this->screenList[$screen->id]);
        }

        // Now, remove it from the screenlist...
        $this->container->screenList->remove($screen);

        return 0;
    }

    public function sceenCount()
    {

    }
}
