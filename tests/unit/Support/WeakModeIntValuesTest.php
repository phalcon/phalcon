<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Support;

use Phalcon\Auth\Adapter\AdapterLocator;
use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Adapter\Adapter;
use Phalcon\Di\Di;
use Phalcon\Support\Debug;
use Phalcon\Support\Debug\Dump;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testSupportAbstractLocatorDefinitionInt(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Definition 123 must implement ' . Adapter::class
        );

        new AdapterLocator(new Di(), ['x' => 123]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testSupportDebugDumpStyleInt(): void
    {
        $dump = new Dump(['str' => 123]);

        $this->assertStringContainsString(
            '<b style="123">String</b>',
            $dump->variable('x')
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testSupportDebugSetBlacklistInt(): void
    {
        $debug = new Debug();

        $this->assertSame(
            $debug,
            $debug->setBlacklist(['request' => [123], 'server' => [456]])
        );
    }
}
