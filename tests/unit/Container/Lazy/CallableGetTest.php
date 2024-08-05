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

final class CallableGetTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyCallableGet(): void
    {
        $lazy     = new CallableGet(stdClass::class);
        $callable = $this->actual($lazy);
        $this->assertInstanceOf(Closure::class, $callable);

        $get1 = $callable();
        $this->assertInstanceOf(stdClass::class, $get1);

        $get2 = $callable();
        $this->assertInstanceOf(stdClass::class, $get2);

        $this->assertSame($get1, $get2);
    }
}
