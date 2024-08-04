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
use Phalcon\Tests\UnitTestCase;

final class UnserializeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: unserialize()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionUnserialize(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $serialized = serialize($data);
        $collection = new Collection();

        $collection->unserialize($serialized);

        $expected = $data;
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);
    }
}
