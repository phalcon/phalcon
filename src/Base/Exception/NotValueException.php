<?php
declare(strict_types=1);
namespace Phalcon\Base\Exception;

class NotValueException extends LogicException
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