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

use Phalcon\Support\Helper\Str\IsPalindrome;
use Phalcon\Tests\UnitTestCase;

final class IsPalindromeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: isPalindrome()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperStrIsPalindrome(): void
    {
        $object = new IsPalindrome();
        $actual = $object('racecar');
        $this->assertTrue($actual);
    }
}
