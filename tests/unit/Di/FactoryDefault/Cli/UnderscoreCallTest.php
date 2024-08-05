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
use Phalcon\Tests\UnitTestCase;

final class UnderscoreCallTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __call()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliUnderscoreCall(): void
    {
        $di = new Di();

        $actual = $di->setEscaper(Escaper::class);

        $this->assertNull($actual);

        $actual = $di->getEscaper();

        $this->assertInstanceOf(Escaper::class, $actual);
    }

    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __call() - unknown method
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-28
     */
    public function testDiFactorydefaultCliUnderscoreCallUnknownMethod(): void
    {
        $di = new Di();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Call to undefined method or service 'notARealMethod'"
        );

        $di->notARealMethod();
    }
}
