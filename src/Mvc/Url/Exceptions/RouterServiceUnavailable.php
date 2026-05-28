<?php

declare(strict_types=1);

namespace Phalcon\Mvc\Url\Exceptions;

use Phalcon\Mvc\Url\Exception;

class RouterServiceUnavailable extends Exception
{
    public function __construct()
    {
        parent::__construct(
            "A dependency injection container is required to access the 'router' service"
        );
    }
}
