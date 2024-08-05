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
use Phalcon\Session\Exception;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class GetSetNameTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: getName()/setName()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetName(): void
    {
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        if (false !== $manager->exists()) {
            $manager->destroy();
        }

        $manager->setName('myname');
        $expected = 'myname';
        $actual   = $manager->getName();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Session\Manager :: getName()/setName() - not valid name
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetNameNotValidName(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The name contains non alphanum characters');

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $manager->setName('%-gga34');
    }

    /**
     * Tests Phalcon\Session\Manager :: getName()/setName() - session started
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetNameSessionStarted(): void
    {
        $valid   = false;
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        try {
            $manager->start();
            $manager->setName('%-gga34');
        } catch (Exception $ex) {
            $manager->destroy();
            $valid    = true;
            $expected = 'Cannot set session name after a session has started';
            $actual   = $ex->getMessage();
            $this->assertEquals($expected, $actual);
        }

        $this->assertTrue($valid);
    }
}
