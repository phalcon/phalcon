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

use Phalcon\Container\Exception\NotDefined;
use Phalcon\Container\Lazy\Env;
use UnitTester;

use function random_int;

class EnvCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyEnv(UnitTester $I): void
    {
        $varname = 'TEST_VAR';
        $lazy = new Env($varname);
        $expected = random_int(1, 100);
        putenv("TEST_VAR={$expected}");

        $actual = $this->actual($lazy);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyEnvType(UnitTester $I): void
    {
        $varname = 'TEST_VAR';
        $lazy = new Env($varname, 'int');
        $expected = random_int(1, 100);
        putenv("TEST_VAR={$expected}");

        $actual = $this->actual($lazy);
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyEnvNoSuchService(UnitTester $I): void
    {
        $varname = 'TEST_VAR_' . random_int(1, 100);
        $I->expectThrowable(
            new NotDefined(
                "Evironment variable '{$varname}' is not defined."
            ),
            function () use ($varname) {
                $lazy = new Env($varname);
                $this->actual($lazy);
            }
        );
    }
}
