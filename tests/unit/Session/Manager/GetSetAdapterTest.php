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
use SessionHandlerInterface;

final class GetSetAdapterTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: getAdapter()/setAdapter()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerGetSetAdapter(): void
    {
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $actual = $manager->getAdapter();
        $class  = SessionHandlerInterface::class;
        $this->assertInstanceOf($class, $actual);
    }
}
