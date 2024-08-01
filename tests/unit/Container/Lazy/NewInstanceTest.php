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

use Capsule\Di\Exception;
use Phalcon\Container\Lazy\NewInstance;
use stdClass;
use UnitTester;

class NewInstanceTest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyStaticCall(UnitTester $I): void
    {
        $lazy = new NewInstance(stdClass::CLASS);
        $new1 = $this->actual($lazy);
        $I->assertInstanceOf(stdClass::CLASS, $new1);

        $new2 = $this->actual($lazy);
        $I->assertInstanceOf(stdClass::CLASS, $new2);

        $I->assertNotSame($new1, $new2);
    }
}
