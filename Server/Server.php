<?php
namespace Theapi\Lcdproc;


use Theapi\Lcdproc\Server\Drivers;

use Theapi\Lcdproc\Server\Clients;
use Theapi\Lcdproc\Server\Client;

// TODO: auto loader (composer)
require_once 'Client.php';
require_once 'Clients.php';
require_once 'Drivers.php';
require_once 'Drivers/Piplate.php';
require_once 'Commands/ClientCommands.php';

class Server
{

  protected $ip;
  protected $port;
  protected $socket;

  // Hold arrays for stream_select to listen to
  protected $streams = array();

  // The clients object
  protected $clients;
  // The drivers object
  protected $drivers;

  public function __construct($driverName = 'piplate') {
    // screenlist_init

    // init_drivers
    $this->drivers = new Drivers();
    $this->drivers->loadDriver($driverName);

    // clients_init
    $this->clients = new Clients();

    // input_init

    // menuscreens_init

    // server_screen_init

  }

  public function run($ip = '127.0.0.1', $port = 13666)
  {
    $this->ip = $ip;
    $this->port = $port;

    $this->socket = stream_socket_server('tcp://' . $this->ip . ':' . $port, $errno, $errstr);
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
    $time = microtime(TRUE);
    */

    do {

      /*
      $lastTime = $time;
      $time = microtime(TRUE);
      $timeDiff = $time - $lastTime;
      */


      $read = $this->streams;
      $write = $error = NULL;

      // sock_poll_clients (with a little blocking)
      $numChanged = stream_select($read, $write, $except, 0, 200000);
      if ($numChanged === FALSE) {
        // Mmm a problem
        break;
      }

      foreach ($read as $stream) {          var_dump(stream_socket_get_name($stream, 1));
        if ($stream === $this->socket) {
          // New client connection
          $conn = stream_socket_accept($this->socket);

          // add the connection to the array to be watched
          $this->streams[] = $conn;
        }
        else {
          $this->handleInput($stream);
        }
      }

      // Time for rendering




    } while (1);

    fclose($this->socket);
  }

  public function removeStream($stream)
  {
    $key = array_search($stream, $this->streams);
    fclose($this->streams[$key]);
    unset($this->clients[$key]);
    unset($this->streams[$key]);
  }

  public function handleInput($stream) {
    $data = fread($stream, 1024);

    if ($data === FALSE || strlen($data) === 0) { // connection closed
      $this->removeStream($stream);
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
      $client = new Client($stream);
      $this->clients->addClient($client);
    }

    $function = array_shift($args);

    switch ($function) {
      case 'hello':
      case 'client_set':
        $client->command($function, $args);
        break;

      case 'debug': // not part of the spec
        $this->fnDebug($stream);
        break;

      default:
        self::sendError($stream, "unkown command\n");
    }

  }

  public function fnDebug($stream) {
    $key = array_search($stream, $this->streams);
    var_dump($stream, $key, $this->clients[$key]);
  }

  public static function sendString($stream, $message) {
    fwrite($stream, $message);
  }

  public static function sendError($stream, $message) {
    fwrite($stream, 'huh? ' . $message);
  }

}
