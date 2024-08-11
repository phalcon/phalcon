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

use Phalcon\Di\Exception;
use Phalcon\Di\FactoryDefault\Cli as Di;
use Phalcon\Html\Escaper;
use Phalcon\Tests\AbstractUnitTestCase;

final class OffsetGetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: offsetGet()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliOffsetGet(): void
    {
        $di = new Di();

        $di->set('escaper', Escaper::class);
        $this->assertInstanceOf(Escaper::class, $di->offsetGet('escaper'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'non-exists' was not found in the dependency injection container"
        );

        $di->offsetGet('non-exists');
    }

    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: offsetGet()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliOffsetGetArray(): void
    {
        $di = new Di();

        $di->set('escaper', Escaper::class);
        $this->assertInstanceOf(Escaper::class, $di['escaper']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'non-exists' was not found in the dependency injection container"
        );

        $di['non-exists'];
    }
}
