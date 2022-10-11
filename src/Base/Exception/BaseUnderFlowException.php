<?php
namespace Phalcon\Base\Exception;

class BaseUnderFlowException extends \UnderflowException
{
    /**
     * Exception thrown when performing an invalid operation on an empty container,
     * such as removing an element.
     *
     * @param string $message
     * @param integer $code
     * @param \UnderflowException|null $previous
     */
    public function __construct(string $message, int $code = 0, \UnderflowException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}