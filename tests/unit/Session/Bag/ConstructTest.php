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

namespace Phalcon\Tests\Unit\Session\Bag;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Session\Bag;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class ConstructTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Bag :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionBagConstruct(): void
    {
        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');
        $collection = new Bag($this->container->get("session"), 'BagTest');

        $class = Bag::class;
        $this->assertInstanceOf($class, $collection);
    }
}
