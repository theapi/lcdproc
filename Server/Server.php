<?php
namespace Theapi\Lcdproc;


use Theapi\Lcdproc\Server\Render;
use Theapi\Lcdproc\Server\ScreenList;
use Theapi\Lcdproc\Server\ServerScreens;
use Theapi\Lcdproc\Server\Config;
use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Drivers;
use Theapi\Lcdproc\Server\Clients;
use Theapi\Lcdproc\Server\Client;

// TODO: auto loader (composer)
require_once 'Config.php';
require_once 'Client.php';
require_once 'Clients.php';
require_once 'Render.php';
require_once 'ScreenList.php';
require_once 'Screen.php';
require_once 'ServerScreens.php';
require_once 'Widget.php';
require_once 'Drivers.php';
require_once 'Drivers/Piplate.php';
require_once 'Commands/ClientCommands.php';
require_once 'Commands/ServerCommands.php';
require_once 'Exception/ClientException.php';

class Server
{
    // $this can be passed as a container,
    // so the initialised classes are available to be used

    // The config object
    public $config;
    // The render object
    public $render;
    // The clients object
    public $clients;
    // The drivers object
    public $drivers;
    // The serverScreen object
    public $screenList;
    // The serverScreen object
    public $serverScreen;

    public $timer = 0;

    protected $ip;
    protected $port;
    protected $socket;

    // Hold arrays for stream_select to listen to
    protected $streams = array();


    public function __construct($driverName = 'piplate')
    {
        // set_default_settings
        $this->config = new Config();

        // screenlist_init
        $this->screenList = new ScreenList($this);

        // init_drivers
        $this->drivers = new Drivers($this->config);
        $this->drivers->loadDriver($driverName);

        // clients_init
        $this->clients = new Clients();

        $this->render = new Render();

        // input_init

        // menuscreens_init

        // server_screen_init
        $this->serverScreen = new ServerScreens($this);
    }

    public function run($ip = '127.0.0.1', $port = 13666)
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->socket = stream_socket_server('tcp://' . $this->ip . ':' . $this->port, $errno, $errstr);
        if (!$this->socket) {
            throw new \Exception('Unable to create ' . $this->ip . ':' . $this->port, $errno);
        }
        $this->streams[] = $this->socket;

        $this->doMainLoop();
    }

    public function doMainLoop()
    {

        /*
         $renderFreq = 2; // Complete guess for now

        $processLag = 0;
        $renderLag = 0;

        // Microtime as a float
        $time = microtime(true);
        */

        do {

            /*
             $lastTime = $time;
            $time = microtime(true);
            $timeDiff = $time - $lastTime;
            */

            $read = $this->streams;
            $write = $error = null;

            // sock_poll_clients (with a little blocking)
            $numChanged = stream_select($read, $write, $except, 0, 500000);
            if ($numChanged === false) {
                // Mmm a problem
                break;
            }

            foreach ($read as $stream) {          //var_dump(stream_socket_get_name($stream, 1));
                if ($stream === $this->socket) {
                    // New client connection
                    $conn = stream_socket_accept($this->socket);

                    // add the connection to the array to be watched
                    $this->streams[] = $conn;
                } else {
                    $this->handleInput($stream);
                }
            }

            // Time for rendering
            $this->timer++;
            $this->screenList->process();
            $screen = $this->screenList->current();
            if ($screen->id == '_server_screen') {
                $this->serverScreen->update();
            }

            $this->render->screen($screen, $this->timer);

        } while (1);

        fclose($this->socket);
    }

    public function removeStream($stream)
    {
        $client = $this->clients->findByStream($stream);
        $this->clients->removeClient($client);

        $key = array_search($stream, $this->streams);
        fclose($this->streams[$key]);
        unset($this->streams[$key]);
    }

    public function handleInput($stream)
    {
        $data = fread($stream, 1024);

        if ($data === false || strlen($data) === 0) {
            $this->removeStream($stream);
            // connection closed
            return;
        }

        $args = explode(' ', trim($data));
        if (count($args) == 0) {
            // send error
            return;
        }

        $client = $this->clients->findByStream($stream);
        if (empty($client)) {
            // a new client
            $client = new Client($this, $stream);
            $this->clients->addClient($client);
        }

        $function = array_shift($args);

        switch ($function) {
            case 'debug': // not part of the spec
                $this->fnDebug($stream);
                break;
            default:
                try {
                    $client->command($function, $args);
                } catch (CLientException $e) {
                    self::sendError($e->getStream(), $e->getMessage());
                }
        }

    }

    public function fnDebug($stream)
    {
        $key = array_search($stream, $this->streams);
        var_dump($stream, $key, $this->clients[$key]);
    }

    public static function sendString($stream, $message)
    {
        if (stream_get_meta_data($stream)) {
            fwrite($stream, $message);
        }
    }


    public static function sendError($stream, $message)
    {
        if (stream_get_meta_data($stream)) {
            fwrite($stream, 'huh? ' . $message . "\n");
        }
    }
}
