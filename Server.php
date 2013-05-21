<?php
namespace Theapi\Piplate\Lcdproc;

class Server
{

  protected $ip;
  protected $port;
  protected $socket;

  // Hold arrays for stream_select to listen to
  protected $streams = array();

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
          // NB ignore the client until they say 'hello'
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
    unset($this->streams[$key]);
  }

  public function handleRead($stream) {
    $data = fread($stream, 1024);

    if ($data === FALSE || strlen($data) === 0) { // connection closed
      $this->removeStream($stream);
      return;
    }

    switch (trim($data)) {
      case 'hello':
        $this->handleHello($stream);
        break;

      default:
        $this->sendHuh($stream);
    }

  }

  /**
   * Client init.
   * You must send this before the server will pay
	 * any attention to you.  You'll get some info about the server
	 * in return...  (a "connect" string)
   */
  public function handleHello($stream) {
    // A little white lie about who we are
    // but the dimensions are correct for the pi plate
    fwrite($stream, "connect LCDproc 0.5dev protocol 0.3 lcd wid 16 hgt 2 cellwid 5 cellhgt 8\n");
  }

  public function sendHuh($stream) {
    fwrite($stream, "huh?\n");
  }

}
