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

use Phalcon\Container\Lazy\Call;
use UnitTester;

class CallCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyCall(UnitTester $I): void
    {
        $lazy = new Call(
            function ($container) {
                return true;
            }
        );

        $actual = $this->actual($lazy);
        $I->assertTrue($actual);
    }
}
