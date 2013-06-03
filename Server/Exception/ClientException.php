<?php
namespace Theapi\Lcdproc\Server\Exception;


use Theapi\Lcdproc\Server\Client;

/**
 * Exception class for when a client makes a bad request.
 */
class ClientException extends \Exception
{

    protected $client;

    public function __construct($client, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->client = $client;
        if ($this->client instanceof Client) {
            $client->container->log(LOG_ERR, $message);
        }
    }
}
