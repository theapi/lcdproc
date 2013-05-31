<?php
namespace Theapi\Lcdproc\Server;

class Clients
{
    protected $clientList = array();

    public function shutdown()
    {

    }

    public function addClient($client)
    {
        $key = (string) $client->getStream();
        $this->clientList[$key] = $client;
    }

    public function removeClient($client)
    {
        $key = (string) $client->getStream();
        if (isset($this->clientList[$key])) {
            unset($this->clientList[$key]);
        }
    }

    public function getFirst()
    {

    }

    public function getNext()
    {

    }

    public function getCount()
    {

    }

    public function findByStream($stream)
    {
        $key = (string) $stream;
        if (isset($this->clientList[$key])) {
            return $this->clientList[$key];
        }

        return null;
    }
}
