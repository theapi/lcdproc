<?php
namespace Theapi\Lcdproc\Server\Exception;


/**
 * Exception class for when a client makes a bad request.
 */
class ClientException extends \Exception
{

    protected $client;

    public function __construct($client, $message = '', $code = 0, $previous = null)
    {
        $this->client = $client;

        $client->container->log(LOG_ERR, $message);
    }

    public function getStream()
    {
        return $this->client->stream;
    }
}
