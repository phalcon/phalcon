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

namespace Phalcon\Tests\Unit\Filter\Sanitize;

use Codeception\Example;
use Phalcon\Filter\Sanitize\BoolVal;
use UnitTester;

class BoolValCest
{
    /**
     * Tests Phalcon\Filter\Sanitize\BoolVal :: __invoke()
     *
     * @dataProvider getData
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterSanitizeBoolValInvoke(UnitTester $I, Example $example)
    {
        $I->wantToTest('Filter\Sanitize\BoolVal - __invoke()');

        $sanitizer = new BoolVal();

        $actual = $sanitizer($example[0]);
        $I->assertEquals($example[1], $actual);
    }

    /**
     * @return array[]
     */
    private function getData(): array
    {
        return [
            [1000, true],
            [0xFFA, true],
            ['1000', true],
            [null, false],
            ['on', true],
            ['off', false],
        ];
    }
}
