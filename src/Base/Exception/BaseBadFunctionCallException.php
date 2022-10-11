<?php
namespace Phalcon\Base\Exception;

use BadFunctionCallException as BadFunctionCallExceptionAlias;

class BaseBadFunctionCallException extends BadFunctionCallExceptionAlias
{

    /**
     * Exception throw if a callback refers to on undefined function or if some arguments are missing.
     *
     * @param string $message
     * @param integer $code
     * @param BadFunctionCallExceptionAlias|null $previous
     */
    public function __construct(string $message, int $code = 0, \BadFunctionCallException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}