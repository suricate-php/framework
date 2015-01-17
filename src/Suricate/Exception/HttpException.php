<?php
namespace Suricate\Exception;


class HttpException extends \RuntimeException
{
    private $statusCode;
    
    public function __construct($statusCode, $message = null, \Exception $previous = null, $code = 0)
    {
        $this->statusCode   = $statusCode;
        $this->headers      = $headers;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
