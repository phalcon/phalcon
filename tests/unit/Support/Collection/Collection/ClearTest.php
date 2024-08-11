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

namespace Phalcon\Tests\Unit\Support\Collection\Collection;

use Phalcon\Support\Collection;
use Phalcon\Tests\AbstractUnitTestCase;

final class ClearTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: clear()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionClear(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data);

        $expected = $data;
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->clear();

        $expected = 0;
        $actual   = $collection->count();
        $this->assertSame($expected, $actual);
    }
}
