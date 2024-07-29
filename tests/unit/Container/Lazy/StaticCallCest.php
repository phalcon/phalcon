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

namespace Phalcon\Tests\Unit\Container\Lazy;

use Phalcon\Container\Lazy\StaticCall;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use UnitTester;

class StaticCallCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyStaticCall(UnitTester $I): void
    {
        $lazy = new StaticCall(
            TestWithInterface::class,
            'staticMethod',
            ['ten']
        );

        $expected = 'ten';
        $actual   = $this->actual($lazy);
        $I->assertSame($expected, $actual);
    }
}
