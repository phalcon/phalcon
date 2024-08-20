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

use Phalcon\Support\Helper\Arr\Flatten;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class FlattenTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Arr :: flatten()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrFlatten(): void
    {
        $object = new Flatten();
        $source = [1, [2], [[3], 4], 5];

        $expected = [1, 2, [3], 4, 5];
        $actual   = $object($source);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Arr :: flatten() - deep
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrFlattenDeep(): void
    {
        $object = new Flatten();
        $source = [1, [2], [[3], 4], 5];

        $expected = [1, 2, 3, 4, 5];
        $actual   = $object($source, true);
        $this->assertSame($expected, $actual);
    }
}
