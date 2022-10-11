<?php
declare(strict_types=1);
namespace Phalcon\Base\Exception;

use BadMethodCallException as BadMethodCallExceptionAlias;

class BadMethodCallException extends BadMethodCallExceptionAlias
{
    /**
     * Exception throw if a callback refers to on undefined method or if some arguments are missing .
     *
     * @param string $message
     * @param integer $code
     * @param BadMethodCallExceptionAlias|null $previous
     * @throw BadMethodCallExceptionAlias
     */
    public function __construct(string $message, int $code = 0, BadMethodCallExceptionAlias $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}