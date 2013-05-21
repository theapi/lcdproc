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
    $write = null;
    $except = null;

    while (1) {
      $changed = stream_select($this->streams, $write, $except, 200000);
      if ($changed === FALSE) {
        // Mmm a problem
        break;
      }

      for ($i = 0; $i < $changed; $i++) {
        if ($this->streams[$i] === $this->socket) {
          // the server has something
          $conn = stream_socket_accept($this->socket);
          // TODO: lcdproc instead of time :)
          fwrite($conn, "Hello! The time is ".date("n/j/Y g:i a")."\n");
          // add the client to the array to be watched
          $this->streams[] = $conn;
        }
        else {

          $sock_data = fread($this->streams[$i], 1024);
          if (strlen($sock_data) === 0) { // connection closed
            $this->removeStream($i);
          }
          else if ($sock_data === FALSE) {
            // something bad happened
            $this->removeStream($i);
          }
          else {
            fwrite($this->streams[$i], "You have sent :[".$sock_data."]\n");
          }

        }
      }
    }

    fclose($this->socket);
  }

  public function removeStream($index)
  {
    fclose($this->streams[$index]);
    unset($this->streams[$index]);
  }

}
