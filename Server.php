<?php
namespace Theapi\Piplate\Lcdproc;

class Server
{

  protected $ip;
  protected $port;
  protected $socket;
  protected $clients = array();

  public function run($ip = '127.0.0.1', $port = 13666)
  {
    $this->ip = $ip;
    $this->port = $port;

    $this->socket = stream_socket_server('tcp://' . $ip . ':' . $port, $errno, $errstr);
    if (!$this->socket) {
      throw new \Exception('Unable to create ' . $this->ip . ':' . $this->port, $errno);
    }

    // TODO: lcdproc instead of time :)
    while ($conn = stream_socket_accept($this->socket)) {
      fwrite($conn, 'The local time is ' . date('n/j/Y g:i a') . "\n");
      fclose($conn);
    }

    fclose($this->socket);

  }

}
