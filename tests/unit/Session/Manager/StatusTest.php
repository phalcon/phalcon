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
use Phalcon\Tests\UnitTestCase;

final class StatusTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: status()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerStatus(): void
    {
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $expected = $manager::SESSION_NONE;
        $actual   = $manager->status();
        $this->assertEquals($expected, $actual);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $expected = $manager::SESSION_ACTIVE;
        $actual   = $manager->status();
        $this->assertEquals($expected, $actual);

        $manager->destroy();

        $expected = $manager::SESSION_NONE;
        $actual   = $manager->status();
        $this->assertEquals($expected, $actual);
    }
}
