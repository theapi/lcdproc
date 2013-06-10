<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Commands\ClientCommands;
use Theapi\Lcdproc\Server\Commands\ServerCommands;
use Theapi\Lcdproc\Server\Commands\ScreenCommands;
use Theapi\Lcdproc\Server\Commands\WidgetCommands;
use Theapi\Lcdproc\Server\Commands\MenuCommands;
use Theapi\Lcdproc\Server\Config;
use Theapi\Lcdproc\Server\Server;

class Client
{
    /**
     * Length of longest transmission allowed at once...
     */
    const MAXMSG = 8192;
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

    public $stream;
    protected $messages = array();
    public $backlight = Render::BACKLIGHT_OPEN;
    public $heartbeat;
    public $state;
    protected $name;
    protected $menu;
    public $screenList = array();

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

    public function __construct($container, $stream)
    {
        $this->container = $container;
        $this->stream = $stream;

        if (is_resource($stream)) {
            stream_set_read_buffer($stream, self::MAXMSG);
        }

        $this->state = self::STATE_NEW;

        $this->clientCommands = new ClientCommands($this);
        $this->serverCommands = new ServerCommands($this);
        $this->screenCommands = new ScreenCommands($this);
        $this->widgetCommands = new WidgetCommands($this);
        $this->menuCommands   = new MenuCommands($this);
    }

    public function command($name, $args)
    {

        if (isset($this->commands[$name])) {
            $commandHandler = $this->commands[$name][0];
            $method = $this->commands[$name][1];
            if (method_exists($this->$commandHandler, $method)) {
                try {
                    $err = $this->$commandHandler->$method($args);
                    if ($err) {
                        throw new ClientException('Function returned error: ' . $method);
                    }
                } catch (ClientException $e) {
                    throw $e;
                }
            } else {
                // oops there's a command mapping mixup
                // TODO: exceptions for coding errors
            }
        } else {
            throw new ClientException('Invalid command ' . $name);
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
        $this->container->log(LOG_DEBUG, 'destroy client:' . (int) $this->stream);

        // Close the socket
        $this->container->removeStream($this->stream);

        // Eat messages
        $this->messages = array();

        // Clean up the screenlist...
        foreach ($this->screenList as $screen) {
            $screen->destroy();
        }
        $this->screenList = array();

        // Free client's other data
        $this->state = self::STATE_GONE;

        return 0;
    }

    /**
     * Add  messages from to the client's queue...
     */
    public function addMessage($message)
    {
        if (!empty($message)) {
            $this->messages[] = $message;
        }
    }

    /**
     * Woo-hoo!  A simple function.  :)
     */
    public function getMessage()
    {
        return array_shift($this->messages);
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

    public function readFromSocket()
    {
        $data = fread($this->stream, self::MAXMSG);

        if ($data === false || strlen($data) === 0) {
            $this->container->clients->removeClient($this);
            return;
        }

        $messages = explode("\n", $data);
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return 0;
    }

    public function sendString($str)
    {
        $this->container->log(LOG_DEBUG, '< ' . (int) $this->stream . ': ' . $str);
        if (!fwrite($this->stream, $str)) {
            $this->container->clients->removeClient($this);
        }
    }

    public function sendError($str)
    {
        $this->container->log(LOG_DEBUG, '< ' . (int) $this->stream . ':huh? ' . $str);
        if (!@fwrite($this->stream, 'huh? ' . $message . "\n")) {
            $this->container->clients->removeClient($this);
        }
    }
}
