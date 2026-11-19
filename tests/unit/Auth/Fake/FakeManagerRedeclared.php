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

namespace Phalcon\Tests\Unit\Auth\Fake;

use Phalcon\Auth\Access\AccessLocator;
use Phalcon\Auth\Manager;

/**
 * A manager that declares the access factory with the cphalcon type
 */
class FakeManagerRedeclared extends Manager
{
    protected ?AccessLocator $accessFactory;
}
