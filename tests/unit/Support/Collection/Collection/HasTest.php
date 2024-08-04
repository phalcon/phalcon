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

final class HasTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: has()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionHas(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data);

        $actual = $collection->has('three');
        $this->assertTrue($actual);

        $actual = $collection->has('THREE');
        $this->assertTrue($actual);

        $actual = $collection->has(uniqid());
        $this->assertFalse($actual);

        $actual = $collection->__isset('three');
        $this->assertTrue($actual);

        $actual = isset($collection['three']);
        $this->assertTrue($actual);

        $actual = isset($collection[uniqid()]);
        $this->assertFalse($actual);

        $actual = $collection->offsetExists('three');
        $this->assertTrue($actual);

        $actual = $collection->offsetExists(uniqid());
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Support\Collection :: has() - sensitive
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionHasSensitive(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Collection($data, false);

        $actual = $collection->has('three');
        $this->assertTrue($actual);

        $actual = $collection->has('THREE');
        $this->assertFalse($actual);

        $actual = $collection->has(uniqid());
        $this->assertFalse($actual);

        $actual = $collection->__isset('three');
        $this->assertTrue($actual);

        $actual = isset($collection['three']);
        $this->assertTrue($actual);

        $actual = isset($collection[uniqid()]);
        $this->assertFalse($actual);

        $actual = $collection->offsetExists('three');
        $this->assertTrue($actual);

        $actual = $collection->offsetExists(uniqid());
        $this->assertFalse($actual);
    }
}
