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

class GetAddSetFilesCest
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader :: getFiles()/addFile()/setFile()
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderGetAddSetFiles(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - getFiles()/addFile()/setFile()');

        $loader = new Loader();

        $I->assertEquals(
            [],
            $loader->getFiles()
        );

        $loader->setFiles(
            [
                'classOne.php',
                'classOne.php',
                'classOne.php',
            ]
        );
        $I->assertEquals(
            [
                'classOne.php',
            ],
            $loader->getFiles()
        );

        /**
         * Clear
         */
        $loader->setFiles([]);
        $I->assertEquals(
            [],
            $loader->getFiles()
        );

        $loader
            ->addFile('classOne.php')
            ->addFile('classTwo.php')
            ->addFile('classOne.php')
        ;
        $I->assertEquals(
            [
                'classOne.php',
                'classTwo.php',
            ],
            $loader->getFiles()
        );
    }
}
