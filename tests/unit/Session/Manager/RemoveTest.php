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

namespace Phalcon\Tests\Unit\Session\Manager;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class RemoveTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerRemove(): void
    {
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $actual = $manager->has('test');
        $this->assertFalse($actual);

        $manager->set('test', 'myval');
        $actual = $manager->has('test');
        $this->assertTrue($actual);

        $manager->remove('test');
        $actual = $manager->has('test');
        $this->assertFalse($actual);

        $manager->destroy();

        $actual = $manager->exists();
        $this->assertFalse($actual);

        $_SESSION = $store;
    }
}
