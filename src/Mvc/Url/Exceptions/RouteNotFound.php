<?php

declare(strict_types=1);

namespace Phalcon\Mvc\Url\Exceptions;

use Phalcon\Mvc\Url\Exception;

class RouteNotFound extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(
            "Cannot obtain a route using the name '" . $name . "'"
        );
    }
}
