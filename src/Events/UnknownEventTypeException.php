<?php

namespace Phalcon\Events;

use Phalcon\Db\Exception;
use Throwable;

class UnknownEventTypeException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Unknown event name: ' . $message, $code, $previous);
    }
}
