<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Access;

use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Access\Access;
use Phalcon\Support\AbstractLocator;

/**
 * Service locator for Phalcon\Auth access gates. Utilizes the container to
 * obtain the service. For the Phalcon\Container\Container one can use
 * autowiring. For the Phalcon\Di\Di, one needs to register the gates in it
 * to be used here.
 *
 * @extends AbstractLocator<Access>
 *
 */
class AccessLocator extends AbstractLocator
{
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    protected function getInterfaceClass(): string
    {
        return Access::class;
    }

    protected function getServices(): array
    {
        return [
            'auth'  => Auth::class,
            'guest' => Guest::class,
        ];
    }
}
