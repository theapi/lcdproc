<?php
namespace Theapi\Lcdproc\Server\Exception;


/**
 * Exception class for when a client makes a bad request.
 */
class ClientException extends \Exception
{

    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
