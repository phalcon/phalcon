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

use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class UnderscoreGetSetTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: __get()/__set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerUnderscoreGetSet(): void
    {
        $manager = new Manager();

        $files = $this->newService('sessionStream');

        $manager->setAdapter($files);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $expected      = 'myval';
        $manager->test = $expected;

        $this->assertEquals($expected, $manager->test);

        $manager->destroy();

        $actual = $manager->exists();
        $this->assertFalse($actual);
    }
}
