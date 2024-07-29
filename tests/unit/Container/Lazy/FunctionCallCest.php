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

use Phalcon\Container\Lazy\FunctionCall;
use UnitTester;

class FunctionCallCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyFunctionCall(UnitTester $I): void
    {
        require_once dataDir('fixtures/Container/functions.php');

        $lazy = new FunctionCall(
            'Phalcon\Tests\Fixtures\Container\test',
            ['ten']
        );
        $actual = $this->actual($lazy);
        $I->assertSame('ten', $actual);
    }
}
