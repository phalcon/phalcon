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

use Phalcon\Container\Lazy\ArrayValues;
use Phalcon\Container\Lazy\Env;
use UnitTester;

class ArrayValuesCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyArrayValues(UnitTester $I): void
    {
        $varname = 'TEST_VAR_ONE';
        $lazy    = new ArrayValues(
            [
                $varname => new Env($varname)
            ]
        );

        $actual = isset($lazy['one']);
        $I->assertFalse($actual);

        $lazy['one'] = 'two';

        $actual = isset($lazy['one']);
        $I->assertTrue($actual);

        $expected = 'two';
        $actual   = $lazy['one'];
        $I->assertSame($expected, $actual);

        unset($lazy['one']);

        $actual = isset($lazy['one']);
        $I->assertFalse($actual);

        $lazy[] = 'three';

        $expected = 2;
        $I->assertCount($expected, $lazy);

        $I->assertSame('three', $lazy[0]);

        foreach ($lazy as $key => $value) {
            if ($key === $varname) {
                $I->assertInstanceOf(Env::CLASS, $value);
            }
        }

        $value = random_int(1, 100);
        putenv("TEST_VAR_ONE={$value}");
        $expect = [$varname => $value, 0 => 'three'];
        $actual = $this->actual($lazy);
        $I->assertEquals($expect, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyArrayValuesRecursion(UnitTester $I): void
    {
        $lazy = new ArrayValues([
            'one' => new Env('TEST_VAR_ONE', 'int'),
            [
                'two' => new Env('TEST_VAR_TWO', 'int'),
            ],
            'three' => 'dib'
        ]);

        $one = random_int(1, 100);
        putenv("TEST_VAR_ONE={$one}");

        $two = random_int(1, 100);
        putenv("TEST_VAR_TWO={$two}");

        $expect = [
            'one' => $one,
            [
                'two' => $two,
            ],
            'three' => 'dib',
        ];

        $actual = $lazy($this->container);
        $I->assertSame($expect, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyArrayValuesMerge(UnitTester $I): void
    {
        $lazy = new ArrayValues(
            [
                'one',
                'two',
                'three' => 'ten',
            ]
        );

        $lazy->merge(
            [
                'four',
                'five',
                'six'   => 'twenty',
            ]
        );

        $expect = [
            'one',
            'two',
            'three' => 'ten',
            'four',
            'five',
            'six'   => 'twenty',
        ];

        $actual = $lazy($this->container);

        $I->assertSame($expect, $actual);
    }
}
