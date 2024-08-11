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

use Phalcon\Support\Helper\Arr\LastKey;
use Phalcon\Tests\AbstractUnitTestCase;

use function strlen;

final class LastKeyTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Arr :: lastKey()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperArrLastKey(): void
    {
        $object     = new LastKey();
        $collection = [
            1 => 'Phalcon',
            3 => 'Framework',
        ];

        $expected = 3;
        $actual   = $object($collection);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Arr :: lastKey() - function
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperArrLastKeyFunction(): void
    {
        $object     = new LastKey();
        $collection = [
            1 => 'Phalcon',
            3 => 'Framework',
        ];

        $expected = 1;
        $actual   = $object(
            $collection,
            function ($element) {
                return strlen($element) < 8;
            }
        );
        $this->assertSame($expected, $actual);
    }
}
