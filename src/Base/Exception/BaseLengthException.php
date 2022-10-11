<?php
namespace Phalcon\Base\Exception;

class BaseLengthException extends \LengthException
{
    /**
     * Exception thrown if a length is invalid.
     *
     * @param string $message
     * @param integer $code
     * @param \LengthException|null $previous
     */
    public function __construct(string $message, int $code = 0, \LengthException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}