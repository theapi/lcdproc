<?php
namespace Theapi\Lcdproc;

use Theapi\Lcdproc\Server\Client;

// TODO: auto loader (composer)
require_once 'Client.php';

class Server
{

  protected $ip;
  protected $port;
  protected $socket;

  // Hold arrays for stream_select to listen to
  protected $streams = array();

  // Clients that have said 'hello'
  protected $clients = array();

  public function run($ip = '127.0.0.1', $port = 13666)
  {
    $this->ip = $ip;
    $this->port = $port;

    $this->socket = stream_socket_server('tcp://' . $ip . ':' . $port, $errno, $errstr);
    if (!$this->socket) {
      throw new \Exception('Unable to create ' . $this->ip . ':' . $this->port, $errno);
    }
    $this->streams[] = $this->socket;

    do {
      $read = $this->streams;
      $write = $error = NULL;
      $numChanged = stream_select($read, $write, $except, NULL);
      if ($numChanged === FALSE) {
        // Mmm a problem
        break;
      }

      foreach ($read as $stream) {  //var_dump(stream_socket_get_name($stream, 1));
        if ($stream === $this->socket) {
          // New client connection
          $conn = stream_socket_accept($this->socket);

          // add the client to the array to be watched
          $this->streams[] = $conn;

        }
        else {
          $this->handleRead($stream);
        }
      }
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

  public function handleRead($stream) {
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

    //TODO: $this->clients->findByStream($stream);
    $client_key = array_search($stream, $this->streams);
    if ($client_key !== FALSE) {
      $client = $this->clients[$client_key];
    }

    $function = array_shift($args);

    switch ($function) {
      case 'hello':

          // Create the client
          $client = new Client($stream);
          $client->funcHello($args);
          $this->clients[] = $client;

        break;
      case 'client_set':
        $this->fnClientSet($stream, $args);
        break;
      case 'debug': // not part of the spec
        $this->fnDebug($stream);
        break;

      default:
        $this->sendHuh($stream);
    }

  }




  /**
   * Set client's name and other info
   */
  public function fnClientSet($stream, $args) {

    if (count($args) == 3) {
      $arg1 = trim($args[1], ' -');
      if ($arg1 == 'name') {

        //TODO: $this->clients->findClientByStream($stream);

        $key = array_search($stream, $this->streams);

        $this->clients[$key]->funcClientSet();

        // send success
        fwrite($stream, "success\n");
        return;
      }
    }

    // bad request
    $this->sendHuh($stream);
  }

  public function fnDebug($stream) {
    $key = array_search($stream, $this->streams);
    var_dump($stream, $key, $this->clients[$key]);
  }

  public function sendHuh($stream) {
    fwrite($stream, "huh?\n");
  }

}
