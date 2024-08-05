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

use Phalcon\Session\Exception;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class GetSetIdTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: getId()/setId()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetId(): void
    {
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $actual = $manager->getId();
        $this->assertEquals('', $actual);

        $id = uniqid();
        $manager->setId($id);

        $actual = $manager->getId();
        $this->assertEquals($id, $actual);

        $manager->destroy();
    }

    /**
     * Tests Phalcon\Session\Manager :: setId() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerSetIdException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'The session has already been started. ' .
            'To change the id, use regenerateId()'
        );

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $manager->start();

        $id = uniqid();
        $manager->setId($id);
    }
}
