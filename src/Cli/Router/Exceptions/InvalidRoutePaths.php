<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Cli\Router\Exceptions;

use Phalcon\Cli\Router\Exception;

class InvalidRoutePaths extends Exception
{
    public function __construct(string $route = '')
    {
        $message = 'The route contains invalid paths';

        if ('' !== $route) {
            $message .= " ('" . $route . "')";
        }

        parent::__construct($message);
    }
}
