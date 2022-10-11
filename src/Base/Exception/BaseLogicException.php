<?php
namespace Phalcon\Base\Exception;

class BaseLogicException extends \LogicException
{
    /**
     * Exception that represents in the params logic. This kind of exception should lead directly to a fix in your code .
     *
     * @param string $message
     * @param integer $code
     * @param \LogicException|null $previous
     */
    public function __construct(string $message, int $code = 0, \LogicException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}