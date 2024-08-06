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

namespace Phalcon\Tests\Unit\Paginator\Adapter\NativeArray;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Paginator\Adapter\NativeArray;

final class SetLimitTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Paginator\Adapter\NativeArray :: setLimit()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testPaginatorAdapterNativearraySetLimit(): void
    {
         $paginator = new NativeArray(
            [
                'data'  => array_fill(0, 30, 'banana'),
                'limit' => 25,
                'page'  => 1,
            ]
        );


        $this->assertEquals(
            25,
            $paginator->getLimit()
        );


        $paginator->setLimit(10);

        $this->assertEquals(
            10,
            $paginator->getLimit()
        );
    }
}
