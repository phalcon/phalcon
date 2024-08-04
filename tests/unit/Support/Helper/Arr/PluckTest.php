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

namespace Phalcon\Tests\Unit\Support\Helper\Arr;

use Phalcon\Support\Helper\Arr\Pluck;
use Phalcon\Tests\UnitTestCase;

final class PluckTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Arr :: pluck()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperArrPluck(): void
    {
        $object     = new Pluck();
        $collection = [
            ['product_id' => 'prod-100', 'name' => 'Desk'],
            ['product_id' => 'prod-200', 'name' => 'Chair'],
        ];

        $expected = ['Desk', 'Chair'];
        $actual   = $object($collection, 'name');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Arr :: pluck() - object
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperArrPluckObject(): void
    {
        $object     = new Pluck();
        $collection = [
            (object)['product_id' => 'prod-100', 'name' => 'Desk'],
            (object)['product_id' => 'prod-200', 'name' => 'Chair'],
        ];

        $expected = ['Desk', 'Chair'];
        $actual   = $object($collection, 'name');
        $this->assertSame($expected, $actual);
    }
}
