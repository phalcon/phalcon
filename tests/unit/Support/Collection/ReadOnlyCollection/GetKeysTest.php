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

namespace Phalcon\Tests\Unit\Support\Collection\ReadOnlyCollection;

use Phalcon\Support\Collection\ReadOnlyCollection;
use Phalcon\Tests\UnitTestCase;

final class GetKeysTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: get()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionReadOnlyCollectionGetKeys(): void
    {
        $keys = [
            'one',
            'three',
            'five',
        ];

        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnlyCollection($data);

        $expected = $keys;
        $actual   = $collection->getKeys();
        $this->assertSame($expected, $actual);

        $data = [
            'one'   => 'two',
            'Three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnlyCollection($data);
        $expected   = $keys;
        $actual     = $collection->getKeys();
        $this->assertSame($expected, $actual);

        $expected = array_keys($data);
        $actual   = $collection->getKeys(false);
        $this->assertSame($expected, $actual);
    }
}
