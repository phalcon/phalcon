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

namespace Phalcon\Tests\Unit\Dispatcher\Fake;

use Phalcon\Mvc\Dispatcher;

/**
 * A dispatcher that declares the module name with the cphalcon type
 */
class FakeDispatcherRedeclared extends Dispatcher
{
    protected string $moduleName = "";
}
