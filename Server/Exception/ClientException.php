<?php
namespace Theapi\Lcdproc\Server\Exception;


/**
 * Exception class for when a client makes a bad request.
 */
class CLientException extends \Exception
{

    protected $stream;

    public function __construct($stream, $message = '', $code = 0, $previous = null)
    {
        $this->stream = $stream;
    }

    public function getStream()
    {
        return $this->stream;
    }

}
