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

use function random_int;

final class EnvTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyEnv(): void
    {
        $varname  = 'TEST_VAR';
        $lazy     = new Env($varname);
        $expected = random_int(1, 100);
        putenv("TEST_VAR={$expected}");

        $actual = $this->actual($lazy);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function testContainerLazyEnvNoSuchService(): void
    {
        $varname = 'TEST_VAR_' . random_int(1, 100);
        $this->expectException(NotDefined::class);
        $this->expectExceptionMessage(
            "Evironment variable '{$varname}' is not defined."
        );

        $lazy = new Env($varname);
        $this->actual($lazy);
    }

    /**
     * @return void
     */
    public function testContainerLazyEnvType(): void
    {
        $varname  = 'TEST_VAR';
        $lazy     = new Env($varname, 'int');
        $expected = random_int(1, 100);
        putenv("TEST_VAR={$expected}");

        $actual = $this->actual($lazy);
        $this->assertSame($expected, $actual);
    }
}
