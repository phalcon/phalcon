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

class GetAddSetClassesCest
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader :: getClasses()/addClass()/setClass()
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderGetAddSetClasses(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - getClasses()/addClass()/setClass()');

        $loader = new Loader();

        $I->assertEquals(
            [],
            $loader->getClasses()
        );

        $loader->setClasses(
            [
                'ome' => 'classOne.php',
                'two' => 'classTwo.php',
            ]
        );
        $I->assertEquals(
            [
                'ome' => 'classOne.php',
                'two' => 'classTwo.php',
            ],
            $loader->getClasses()
        );

        /**
         * Clear
         */
        $loader->setClasses([]);
        $I->assertEquals(
            [],
            $loader->getClasses()
        );

        $loader
            ->addClass('one', 'classOne.php')
            ->addClass('two', 'classTwo.php')
            ->addClass('one', 'classOne.php')
        ;
        $I->assertEquals(
            [
                'one' => 'classOne.php',
                'two' => 'classTwo.php',
            ],
            $loader->getClasses()
        );
    }
}
