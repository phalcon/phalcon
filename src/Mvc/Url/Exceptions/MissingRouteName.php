<?php

declare(strict_types=1);

namespace Phalcon\Mvc\Url\Exceptions;

use Phalcon\Mvc\Url\Exception;

class MissingRouteName extends Exception
{
    public function __construct()
    {
        parent::__construct(
            "It's necessary to define the route name with the parameter 'for'"
        );
    }
}
