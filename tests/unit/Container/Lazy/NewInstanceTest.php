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

use Phalcon\Container\Lazy\NewInstance;
use stdClass;

final class NewInstanceTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyStaticCall(): void
    {
        $lazy = new NewInstance(stdClass::CLASS);
        $new1 = $this->actual($lazy);
        $this->assertInstanceOf(stdClass::CLASS, $new1);

        $new2 = $this->actual($lazy);
        $this->assertInstanceOf(stdClass::CLASS, $new2);

        $this->assertNotSame($new1, $new2);
    }
}
