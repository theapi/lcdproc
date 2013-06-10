<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Log;
use Theapi\Lcdproc\Server\Render;
use Theapi\Lcdproc\Server\ScreenList;
use Theapi\Lcdproc\Server\ServerScreens;
use Theapi\Lcdproc\Server\Config;
use Theapi\Lcdproc\Server\Exception\ClientException;
use Theapi\Lcdproc\Server\Drivers;
use Theapi\Lcdproc\Server\Clients;
use Theapi\Lcdproc\Server\Client;

// TODO: auto loader (composer)
require_once 'Log.php';
require_once 'Config.php';
require_once 'Parse.php';
require_once 'Client.php';
require_once 'Clients.php';
require_once 'Render.php';
require_once 'ScreenList.php';
require_once 'Screen.php';
require_once 'ServerScreens.php';
require_once 'Widget.php';
require_once 'Driver.php';
require_once 'Drivers.php';
require_once 'Drivers/Piplate.php';
require_once 'Drivers/Ncurses.php';
require_once 'Commands/ClientCommands.php';
require_once 'Commands/MenuCommands.php';
require_once 'Commands/ScreenCommands.php';
require_once 'Commands/ServerCommands.php';
require_once 'Commands/WidgetCommands.php';
require_once 'Exception/ClientException.php';

class Server
{

    // We want 8 frames per second
    const RENDER_FREQ = 8;
    // And 32 times per second processing of messages and keypresses.
    const PROCESS_FREQ = 32;
    // Allow the rendering strokes to lag behind this many frames.
    // More lag will not be corrected, but will cause slow-down.
    const MAX_RENDER_LAG_FRAMES = 6;

    // $this can be passed as a container,
    // so the initialised classes are available to be used

    // The logger
    public $log;
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


    public function __construct($config = array())
    {

        $defaultOpts = array(
            'driver' => 'piplate',
            'verbosity' => LOG_ERR,
            'serverScreen' => 1,
        );
        $opts = array_merge($defaultOpts, $config);

        $this->log = new Log($opts['verbosity']);

        // set_default_settings
        $this->config = new Config($opts);

        // screenlist_init
        $this->screenList = new ScreenList($this);

        // init_drivers
        $this->drivers = new Drivers($this);
        $this->drivers->loadDriver($opts['driver']);

        // clients_init
        $this->clients = new Clients($this);

        $this->render = new Render($this);

        // input_init

        // menuscreens_init

        // server_screen_init
        $this->serverScreen = new ServerScreens($this);
    }

    public function log($priority, $message)
    {
        $this->log->msg($priority, $message);
    }

    public function run($ip = '127.0.0.1', $port = 13666)
    {
        $this->ip = $ip;
        $this->port = $port;

        $this->socket = stream_socket_server('tcp://' . $this->ip . ':' . $this->port, $errno, $errstr);
        if (!$this->socket) {
            throw new \Exception('Unable to create ' . $this->ip . ':' . $this->port, $errno);
        }
        $key = (int) $this->socket;
        $this->streams[$key] = $this->socket;

        $this->doMainLoop();
    }

    public function doMainLoop()
    {


        // Allow the rendering strokes to lag behind this many frames.
        // More lag will not be corrected, but will cause slow-down.
        $timeUnit = (1e6 / self::RENDER_FREQ);
        $processLag = 0;
        $renderLag = 0;

        // Get initial time
        $t = gettimeofday();


        do {


            // Get current time
            $lastTime = $t;
            $t = gettimeofday();
            $timeDiff = $t['sec'] - $lastTime['sec'];

            if ( (($timeDiff + 1) > (PHP_INT_MAX / 1e6)) || ($timeDiff < 0) ) {
                // We're going to overflow the calculation - probably been to sleep, fudge the values
                $timeDiff = 0;
                $processLag = 1;
                $renderLag = $timeUnit;
            } else {
                $timeDiff *= 1e6; // 1,000,000
                $timeDiff += $t['usec'] - $lastTime['usec'];
            }
            $processLag += $timeDiff;

            if ($processLag > 0) {
                // setup arrays for stream_select()
                $read = $this->streams;
                $write = $error = null;

                // sock_poll_clients (with a little blocking)
                $numChanged = stream_select($read, $write, $except, 0, 200000);
                if ($numChanged === false) {
                    // Mmm a problem
                    break;
                }

                foreach ($read as $stream) {
                    if ($stream === $this->socket) {
                        // New client connection
                        $conn = stream_socket_accept($this->socket);

                        // add the connection to the array to be watched
                        $key = (int) $conn;
                        $this->streams[$key] = $conn;

                        // a new client
                        $client = new Client($this, $conn);
                        $this->clients->addClient($client);

                    } else {
                        $client = $this->clients->findByStream($stream);
                        if (!$client instanceof Client) {
                            $this->removeStream($stream);
                        } else {
                            // get the input from the clients
                            $client->readFromSocket();
                        }
                    }
                }
                // We've done the job...
                $processLag = 0 - (1e6 / self::PROCESS_FREQ);


                // analyze input from network clients and process functions
                $this->clients->parseAllMessages();

                // handle key input from devices
                $this->handleInput();
            }

            $renderLag += $timeDiff;
            if ($renderLag > 0) {
                // Time for a rendering stroke
                $this->timer++;
                $this->screenList->process();
                $s = $this->screenList->current();
                if ($s) {
                    if ($s->id == '_server_screen') {
                        $this->serverScreen->update();
                    }

                    $this->render->screen($s, $this->timer);
                }

                // We've done the job...
                if ($renderLag > (1e6 / self::RENDER_FREQ) * self::MAX_RENDER_LAG_FRAMES) {
                    // Cause rendering slowdown because too much lag
                    $renderLag = (1e6 / self::RENDER_FREQ) * self::MAX_RENDER_LAG_FRAMES;
                }
                $renderLag -= (1e6 / self::RENDER_FREQ);
            }

            // Sleep just as long as needed
            $sleeptime = min(0 - $processLag, 0 - $renderLag);
            if ($sleeptime > 0) {
        			usleep($sleeptime);
        		}

        } while (1);

        fclose($this->socket);
    }

    public function removeStream($stream)
    {
        $key = (int) $stream;

        $this->log(LOG_DEBUG, 'removeStream:' . $key);

        if (isset($this->streams[$key])) {
            fclose($this->streams[$key]);
            unset($this->streams[$key]);
        }
    }


    public function handleInput()
    {

    }
}
