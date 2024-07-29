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

use Closure;
use Phalcon\Container\Lazy\CallableGet;
use stdClass;
use UnitTester;

class CallableGetCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyCallableGet(UnitTester $I): void
    {
        $lazy     = new CallableGet(stdClass::class);
        $callable = $this->actual($lazy);
        $I->assertInstanceOf(Closure::class, $callable);

        $get1 = $callable();
        $I->assertInstanceOf(stdClass::class, $get1);

        $get2 = $callable();
        $I->assertInstanceOf(stdClass::class, $get2);

        $I->assertSame($get1, $get2);
    }
}
