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
use UnitTester;

use function dataDir;

class RequireFileCest extends AbstractLazyTest
{
    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyRequireFile(UnitTester $I): void
    {
        $lazy = new RequireFile(dataDir('fixtures/Container/includeFile.php'));

        $expect = 'included';
        $actual = $this->actual($lazy);
        $I->assertSame($expect, $actual);
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     */
    public function containerLazyRequireFileLazy(UnitTester $I): void
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
        $I->assertSame($expect, $actual);
    }
}
