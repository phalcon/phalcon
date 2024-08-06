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

namespace Phalcon\Tests\Unit\Paginator\PaginatorFactory;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Paginator\Adapter\AdapterInterface;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\PaginatorFactory;

final class NewInstanceTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Paginator\AdapterFactory :: newInstance()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testPaginatorAdapterFactoryNewInstance(): void
    {
        $factory = new PaginatorFactory();
        $adapter = $factory->newInstance('nativeArray');

        $this->assertInstanceOf(NativeArray::class, $adapter);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
