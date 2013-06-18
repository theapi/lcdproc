<?php
namespace Theapi\Lcdproc\Server\Commands;


use Theapi\Lcdproc\Server\Server;
use Theapi\Lcdproc\Server\Exception\ClientException;

class ServerCommands
{

    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
        $this->container = $this->client->container;
    }

    /**
     * Sets the state of the output port
     *
     * (so does nothing)
     */
    public function output($args)
    {
        if (!$this->client->isActive()) {
            throw new ClientException();
        }

        // Lie :)
        $this->client->sendString("success\n");
    }

    /**
     * The sleep_func was intended to make the server sleep for some seconds.
     * This function is currently ignored as making the server sleep actually
     * stalls it and disrupts other clients.
     */
    public function sleep($args)
    {
        if (!$this->client->isActive()) {
            throw new ClientException();
        }

        $this->client->sendString("success\n");
    }

    /**
     * Does nothing, returns "noop complete" message.
     */
    public function noop($args)
    {
        if (!$this->client->isActive()) {
            throw new ClientException();
        }

        $this->client->sendString("success\n");
    }

    /**
     * Tells the unconneted drivers try to reconnect.
     *
     * Not part of lcdproc spec
     */
    public function connect($args)
    {
        if (!$this->client->isActive()) {
            throw new ClientException();
        }
        $this->container->drivers->connect();
        $this->client->sendString("success\n");
    }
}
