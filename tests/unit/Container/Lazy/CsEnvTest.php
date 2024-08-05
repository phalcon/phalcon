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

use Phalcon\Container\Lazy\CsEnv;

final class CsEnvTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyCsEnv(): void
    {
        $varname = 'TEST_VAR';
        $lazy    = new CsEnv($varname, 'int');

        $expected = array_fill(0, 3, random_int(1, 100));
        putenv("TEST_VAR=" . implode(',', $expected));

        $actual = $this->actual($lazy);
        $this->assertEquals($expected, $actual);
    }
}
