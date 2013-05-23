<?php
namespace Theapi\Lcdproc\Server;

class Clients
{
  protected $clientList = array();

  public function shutdown() {

  }

  public function addClient($client) {
    $key = (string) $client->getStream(); // nope :(
    $this->clientList[$key] = $client; var_dump($key);
  }

  public function removeClient($client) {

  }

  public function getFirst() {

  }

  public function getNext() {

  }

  public function getCount() {

  }

  public function findByStream($stream) {
    $key = (string) $stream; //var_dump($key, $this->clientList);
    if (isset($this->clientList[$key])) {
      return $this->clientList[$key];
    }
    return NULL;
  }

}
