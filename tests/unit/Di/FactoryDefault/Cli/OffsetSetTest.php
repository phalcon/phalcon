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
use Phalcon\Encryption\Crypt;
use Phalcon\Html\Escaper;
use Phalcon\Tests\UnitTestCase;

final class OffsetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: offsetSet()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliOffsetSet(): void
    {
        $di = new Di();

        $di->offsetSet('escaper', Escaper::class);

        $actual = $di->offsetGet('escaper');

        $this->assertInstanceOf(Escaper::class, $actual);

        $di['crypt'] = new Crypt();

        $actual = $di->offsetGet('crypt');

        $this->assertInstanceOf(Crypt::class, $actual);
    }
}
