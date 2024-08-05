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

use Phalcon\Container\Lazy\FunctionCall;

use function supportDir;

final class FunctionCallTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyFunctionCall(): void
    {
        require_once dataDir('fixtures/Container/functions.php');

        $lazy   = new FunctionCall(
            'Phalcon\Tests\Fixtures\Container\test',
            ['ten']
        );
        $actual = $this->actual($lazy);
        $this->assertSame('ten', $actual);
    }
}
