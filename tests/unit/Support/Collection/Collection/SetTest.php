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

final class SetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection :: set()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionSet(): void
    {
        $collection = new Collection();

        $collection->set('three', 'two');

        $expected = 'two';
        $actual   = $collection->get('three');
        $this->assertSame($expected, $actual);

        $collection->three = 'Phalcon';

        $expected = 'Phalcon';
        $actual   = $collection->get('three');
        $this->assertSame($expected, $actual);

        $collection->offsetSet('three', 123);

        $expected = 123;
        $actual   = $collection->get('three');
        $this->assertSame($expected, $actual);

        $collection['three'] = true;

        $actual = $collection->get('three');
        $this->assertTrue($actual);
    }
}
