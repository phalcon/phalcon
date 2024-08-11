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

final class HasTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: has()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerHas(): void
    {
        $manager = new Manager();

        $files = $this->newService('sessionStream');

        $manager->setAdapter($files);

        $actual = $manager->has('test');
        $this->assertFalse($actual);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $actual = $manager->has('test');
        $this->assertFalse($actual);

        $manager->set('test', 'myval');

        $actual = $manager->has('test');
        $this->assertTrue($actual);

        $manager->destroy();

        $actual = $manager->exists();
        $this->assertFalse($actual);
    }
}
