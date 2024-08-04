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

use Phalcon\Support\Collection\Exception;
use Phalcon\Support\Collection\ReadOnlyCollection;
use Phalcon\Tests\UnitTestCase;

final class RemoveTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionRemove(): void
    {
        $data       = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
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
    public function testSupportCollectionRemoveInsensitive(): void
    {
        $data       = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
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
    public function testSupportCollectionRemoveOffsetUnset(): void
    {
        $data       = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
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
    public function testSupportCollectionRemoveUnderscoreUnset(): void
    {
        $data       = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
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
    public function testSupportCollectionRemoveUnset(): void
    {
        $data       = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
        $collection = new ReadOnlyCollection($data);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        unset($collection['five']);
    }
}
