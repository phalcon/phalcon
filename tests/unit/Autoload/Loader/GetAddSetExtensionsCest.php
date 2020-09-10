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

class GetAddSetExtensionsCest
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader ::
     * getExtensions()/addExtension()/setExtension()
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderGetAddSetExtensions(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - getExtensions()/addExtension()/setExtension()');

        $loader = new Loader();

        $I->assertEquals(
            [
                'php',
            ],
            $loader->getExtensions()
        );

        $loader->setExtensions(
            [
                'inc',
                'inc',
                'inc',
            ]
        );
        $I->assertEquals(
            [
                'php',
                'inc',
            ],
            $loader->getExtensions()
        );

        /**
         * Clear
         */
        $loader->setExtensions([]);
        $I->assertEquals(
            [
                'php',
            ],
            $loader->getExtensions()
        );

        $loader
            ->addExtension('inc')
            ->addExtension('phpt')
            ->addExtension('inc')
        ;
        $I->assertEquals(
            [
                'php',
                'inc',
                'phpt',
            ],
            $loader->getExtensions()
        );
    }
}
