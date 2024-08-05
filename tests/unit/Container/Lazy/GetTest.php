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

use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Lazy\Get;
use stdClass;

final class GetTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyGet(): void
    {
        $lazy = new Get(stdClass::class);
        $get1 = $this->actual($lazy);
        $this->assertInstanceOf(stdClass::class, $get1);

        $get2 = $this->actual($lazy);
        $this->assertInstanceOf(stdClass::class, $get2);

        $this->assertSame($get1, $get2);
    }

    /**
     * @return void
     */
    public function testContainerLazyGetNoSuchClass(): void
    {
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('NoSuchClass');

        $lazy = new Get('NoSuchClass');
        $this->actual($lazy);
    }
}
