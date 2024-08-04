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

final class SetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionSet(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection = new ReadOnlyCollection();
        $collection->set('three', 123);
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionSetProperty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection        = new ReadOnlyCollection();
        $collection->three = 'Phalcon';
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionOffsetSet(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection = new ReadOnlyCollection();
        $collection->offsetSet('three', 123);
    }

    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionSetArray(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The object is read only');
        $collection          = new ReadOnlyCollection();
        $collection['three'] = true;
    }
}
