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

namespace Phalcon\Tests\Unit\Support\Collection;

use Phalcon\Support\Collection;
use Phalcon\Support\Collection\Exception;
use Phalcon\Support\Collection\ReadOnlyCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class RemoveTest extends AbstractCollectionTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemove(): void
    {
        $data = $this->getData();
        $collection = new Collection($data);

        $expected = $data;
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->remove('five');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->remove('FIVE');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->init($data);
        unset($collection['five']);
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->init($data);
        $collection->__unset('five');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);

        $collection->init($data);
        $collection->offsetUnset('five');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $actual   = $collection->toArray();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemoveException(): void
    {
        $data = $this->getData();
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection->remove('five');
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemoveInsensitiveException(): void
    {
        $data = $this->getData();
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection->remove('FIVE');
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemoveOffsetUnsetException(): void
    {
        $data = $this->getData();
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection->offsetUnset('five');
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemoveUnderscoreUnsetException(): void
    {
        $data = $this->getData();
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection->__unset('five');
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportCollectionRemoveUnsetException(): void
    {
        $data = $this->getData();
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        unset($collection['five']);
    }
}
