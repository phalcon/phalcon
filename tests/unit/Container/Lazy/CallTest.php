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

use Phalcon\Container\Lazy\Call;

final class CallTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyCall(): void
    {
        $lazy = new Call(
            function ($container) {
                return true;
            }
        );

        $actual = $this->actual($lazy);
        $this->assertTrue($actual);
    }
}
