<?php

namespace Phalcon\Db\Event;

use Phalcon\Db\Exception;
use Throwable;

class UnknownEventTypeException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Unknown model event name: ' . $message, $code, $previous);
    }
}
