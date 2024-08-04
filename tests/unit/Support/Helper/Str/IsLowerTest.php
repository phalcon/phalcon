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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Phalcon\Support\Helper\Str\IsLower;
use Phalcon\Tests\UnitTestCase;

final class IsLowerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: isLower()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperStrIsLower(): void
    {
        $object = new IsLower();
        $actual = $object('phalcon framework');
        $this->assertTrue($actual);

        $actual = $object('Phalcon Framework');
        $this->assertFalse($actual);
    }
}
