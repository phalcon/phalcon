<?php
namespace Phalcon\Base\Exception;

class BaseNotValueException extends BaseLogicException
{

    /**
     * Custom framework exception which is thrown calling an empty argument.
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