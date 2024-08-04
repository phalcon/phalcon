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

final class GetValuesTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: get()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionReadOnlyCollectionGetValues(): void
    {
        $data = [
            'one'   => 'two',
            'Three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnlyCollection($data);

        $expected = [
            'two',
            'four',
            'six',
        ];

        $actual = $collection->getValues();
        $this->assertSame($expected, $actual);
    }
}
