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

namespace Phalcon\Tests\Unit\Dispatcher;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Dispatcher\Fake\FakeDispatcherRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A dispatcher can declare the module name with the cphalcon type.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testDispatcherRedeclaredModuleName(): void
    {
        $dispatcher = new FakeDispatcherRedeclared();
        $dispatcher->setModuleName('admin');

        $this->assertSame('admin', $dispatcher->getModuleName());
    }
}
