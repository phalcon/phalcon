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
use UnitTester;

class GetTest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyGet(UnitTester $I): void
    {
        $lazy = new Get(stdClass::class);
        $get1 = $this->actual($lazy);
        $I->assertInstanceOf(stdClass::class, $get1);

        $get2 = $this->actual($lazy);
        $I->assertInstanceOf(stdClass::class, $get2);

        $I->assertSame($get1, $get2);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyGetNoSuchClass(UnitTester $I): void
    {
        $I->expectThrowable(
            new NotFound('NoSuchClass'),
            function () {
                $lazy = new Get('NoSuchClass');
                $this->actual($lazy);
            }
        );
    }
}
