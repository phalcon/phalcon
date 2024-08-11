<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Filter\Filter;

use Phalcon\Filter\Filter;
use Phalcon\Tests\Fixtures\Filter\Sanitize\IPv4;
use Phalcon\Tests\AbstractUnitTestCase;

final class CustomTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter :: custom has()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testFilterFilterCustomHas(): void
    {
        $actual = (new Filter(['ipv4' => IPv4::class]))->has('ipv4');
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Filter :: custom sanitizer
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testFilterFilterCustomSanitizer(): void
    {
        /** @var IPv4 $sanitizer */
        $sanitizer = (new Filter(['ipv4' => IPv4::class]))->get('ipv4');

        $this->assertInstanceOf(IPv4::class, $sanitizer);
        $this->assertSame('127.0.0.1', $sanitizer('127.0.0.1'));
        $this->assertFalse($sanitizer('127.0.0'));
    }
}
