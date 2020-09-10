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

namespace Phalcon\Tests\Unit\Autoload\Loader;

use Phalcon\Autoload\Loader;
use Phalcon\Tests\Fixtures\Traits\LoaderTrait;
use UnitTester;

use function dataDir;
use function function_exists;

class LoadFilesCest
{
    use LoaderTrait;

    public function autoloaderLoaderLoadFiles(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - loadFiles()');

        $loader = new Loader();

        $I->assertFalse(
            function_exists('noClassFoo')
        );

        $I->assertFalse(
            function_exists('noClassBar')
        );

        $loader
            ->addFile(
                dataDir('fixtures/Loader/Example/Functions/FunctionsNoClass.php')
            )
            ->addFile(
                '/path/to/unknown/file'
            )
        ;

        $loader->loadFiles();

        $I->assertTrue(
            function_exists('noClassFoo')
        );

        $I->assertTrue(
            function_exists('noClassBar')
        );
    }
}
