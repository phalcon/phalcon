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
use Phalcon\Container\Lazy\CallableNew;
use stdClass;
use UnitTester;

class CallableNewCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyCallableNew(UnitTester $I): void
    {
        $lazy = new CallableNew(stdClass::CLASS);
        $callable = $this->actual($lazy);
        $I->assertInstanceOf(Closure::CLASS, $callable);

        $new1 = $callable();
        $I->assertInstanceOf(stdClass::CLASS, $new1);

        $new2 = $callable();
        $I->assertInstanceOf(stdClass::CLASS, $new2);

        $I->assertNotSame($new1, $new2);
    }
}
