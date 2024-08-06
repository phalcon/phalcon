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

namespace Phalcon\Tests\Unit\Di\FactoryDefault\Cli;

use Phalcon\Di\FactoryDefault\Cli as Di;
use Phalcon\Tests\UnitTestCase;

final class OffsetExistsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: offsetExists()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliOffsetExists(): void
    {
        $di = new Di();

        $this->assertTrue(
            $di->offsetExists('escaper')
        );

        $this->assertFalse(
            $di->offsetExists('unknownservice')
        );
    }
}
