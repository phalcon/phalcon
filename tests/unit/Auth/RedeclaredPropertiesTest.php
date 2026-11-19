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

namespace Phalcon\Tests\Unit\Auth;

use Phalcon\Auth\Access\AccessLocator;
use Phalcon\Di\Di;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Auth\Fake\FakeManagerRedeclared;

final class RedeclaredPropertiesTest extends AbstractUnitTestCase
{
    /**
     * A manager can declare the access factory with the cphalcon type.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testAuthManagerRedeclaredAccessFactory(): void
    {
        $manager = new FakeManagerRedeclared(new AccessLocator(new Di()));

        $this->assertArrayHasKey('auth', $manager->getAccessList());
    }
}
