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
use Phalcon\Container\Lazy\RequireFile;

use function dataDir;
use function supportDir;

final class RequireFileTest extends AbstractLazyBase
{
    /**
     * @return void
     */
    public function testContainerLazyRequireFile(): void
    {
        $lazy = new RequireFile(dataDir('fixtures/Container/includeFile.php'));

        $expect = 'included';
        $actual = $this->actual($lazy);
        $this->assertSame($expect, $actual);
    }

    /**
     * @return void
     */
    public function testContainerLazyRequireFileLazy(): void
    {
        $lazy = new RequireFile(
            new Call(
                function ($container) {
                    return dataDir('fixtures/Container/includeFile.php');
                }
            )
        );

        $expect = 'included';
        $actual = $this->actual($lazy);
        $this->assertSame($expect, $actual);
    }
}
