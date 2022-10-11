<?php
namespace Phalcon\Base\Exception;

class BaseOutOfBoundsException extends \OutOfBoundsException
{

    /**
     * Exception thrown if a value is not a valid key. This represents errors that cannot be detected at compile time.
     *
     * @param string $message
     * @param integer $code
     * @param \OutOfBoundsException|null $previous
     */
    public function __construct(string $message, int $code = 0, \OutOfBoundsException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}