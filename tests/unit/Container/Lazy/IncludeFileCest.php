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
use Phalcon\Container\Lazy\IncludeFile;
use UnitTester;

use function dataDir;

class IncludeFileCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyIncludeFile(UnitTester $I): void
    {
        $lazy = new IncludeFile(dataDir('fixtures/Container/includeFile.php'));

        $expected = 'included';
        $actual   = $this->actual($lazy);
        $I->assertSame($expected, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyIncludeFileLazy(UnitTester $I): void
    {
        $lazy = new IncludeFile(
            new Call(
                function ($container) {
                    return dataDir('fixtures/Container/includeFile.php');
                }
            )
        );

        $expected = 'included';
        $actual   = $this->actual($lazy);
        $I->assertSame($expected, $actual);
    }
}
