<?php
namespace Theapi\Lcdproc\Server;

use Theapi\Lcdproc\Server\Parse;
use Theapi\Lcdproc\Server\Client;

class Clients
{
    protected $clientList = array();
    protected $container;


    public function __construct($container)
    {
        $this->container = $container;
    }

    public function shutdown()
    {

    }

    public function addClient($client)
    {
        $key = (int) $client->getStream();
        $this->clientList[$key] = $client;
    }

    public function removeClient($client)
    {
        $key = (int) $client->getStream();
        $this->container->log(LOG_DEBUG, 'removeClient:' . $key);

        $client->destroy();
        $client = null;

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
        $key = (int) $stream;
        if (isset($this->clientList[$key])) {
            return $this->clientList[$key];
        }

        return null;
    }

    public function parseAllMessages() {
        foreach ($this->clientList as $c) {
            if (!$c instanceof Client) {
                continue;
            }
            // parse all its messages...
            while ($str = $c->getMessage()) {
                $this->container->log(LOG_DEBUG, '> ' . $str);
                Parse::message($str, $c);
                if ($c->state == Client::STATE_GONE) {
                    $this->removeClient($c);
                }
            }
        }
    }
}
