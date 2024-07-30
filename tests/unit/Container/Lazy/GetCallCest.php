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

use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Lazy\GetCall;
use Phalcon\Tests\Fixtures\Container\TestWithInterface;
use UnitTester;

class GetCallCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyGetCall(UnitTester $I): void
    {
        $lazy = new GetCall(TestWithInterface::class, 'getValue', []);
        $actual = $this->actual($lazy);

        $expected = 'two';
        $actual   = $this->actual($lazy);
        $I->assertSame($expected, $actual);
    }

    /**
     * @return Definitions
     */
    protected function definitions(): Definitions
    {
        $definitions = parent::definitions();
        $definitions->{TestWithInterface::class}->argument('one', 'ten');

        return $definitions;
    }
}
