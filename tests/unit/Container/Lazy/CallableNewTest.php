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
use Phalcon\Container\Lazy\CallableNew;
use stdClass;

final class CallableNewTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyCallableNew(): void
    {
        $lazy     = new CallableNew(stdClass::CLASS);
        $callable = $this->actual($lazy);
        $this->assertInstanceOf(Closure::CLASS, $callable);

        $new1 = $callable();
        $this->assertInstanceOf(stdClass::CLASS, $new1);

        $new2 = $callable();
        $this->assertInstanceOf(stdClass::CLASS, $new2);

        $this->assertNotSame($new1, $new2);
    }
}
