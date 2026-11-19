<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cli\Router\Route\Fake;

use Phalcon\Cli\Router\Route;

/**
 * A route that declares the delimiter with the cphalcon type
 */
class FakeRouteRedeclared extends Route
{
    protected string $delimiter;
}
