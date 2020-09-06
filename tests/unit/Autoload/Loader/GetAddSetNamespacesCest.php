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

use Phalcon\Autoload\Exception;
use Phalcon\Autoload\Loader;
use Phalcon\Tests\Fixtures\Traits\LoaderTrait;
use UnitTester;

class GetAddSetNamespacesCest
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader ::
     * getNamespaces()/addNamespace()/setNamespace()
     *
     * @throws Exception
     * @since  2018-11-13
     */
    public function autoloaderLoaderGetAddSetNamespaces(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - getNamespaces()/addNamespace()/setNamespace()');

        $loader = new Loader();

        $I->assertEquals(
            [],
            $loader->getNamespaces()
        );

        $loader->setNamespaces(
            [
                'Phalcon\Loader'   => '/path/to/loader',
                'Phalcon\Provider' => [
                    '/path/to/provider/source',
                    '/path/to/provider/target',
                ],
            ]
        );
        $I->assertEquals(
            [
                'Phalcon\Loader\\'   => [
                    '/path/to/loader/',
                ],
                'Phalcon\Provider\\' => [
                    '/path/to/provider/source/',
                    '/path/to/provider/target/',
                ],
            ],
            $loader->getNamespaces()
        );

        /**
         * Clear
         */
        $loader->setNamespaces([]);
        $I->assertEquals(
            [],
            $loader->getNamespaces()
        );

        $loader
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/loader'
            )
            ->addNamespace(
                'Phalcon\Provider',
                [
                    '/path/to/provider/source',
                    '/path/to/provider/target',
                ]
            )
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/loader'
            )
        ;
        $I->assertEquals(
            [
                'Phalcon\Loader\\'   => [
                    '/path/to/loader/',
                ],
                'Phalcon\Provider\\' => [
                    '/path/to/provider/source/',
                    '/path/to/provider/target/',
                ],
            ],
            $loader->getNamespaces()
        );

        /**
         * Clear - prepend
         */
        $loader->setNamespaces([]);
        $I->assertEquals(
            [],
            $loader->getNamespaces()
        );

        $loader
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/loader'
            )
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/provider/source'
            )
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/provider/target',
                true
            )
            ->addNamespace(
                'Phalcon\Loader',
                '/path/to/provider/source'
            )
        ;
        $I->assertEquals(
            [
                'Phalcon\Loader\\' => [
                    '/path/to/provider/target/',
                    '/path/to/loader/',
                    '/path/to/provider/source/',
                ],
            ],
            $loader->getNamespaces()
        );
    }

    /**
     * Tests Phalcon\Autoload\Loader ::
     * getNamespaces()/addNamespace()/setNamespace() - exception
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderGetAddSetNamespacesException(UnitTester $I)
    {
        $I->wantToTest(
            'Autoload\Loader - getNamespaces()/addNamespace()/setNamespace() - exception'
        );

        $I->expectThrowable(
            new Exception(
                'The directories parameter is not a string or array'
            ),
            function () {
                $loader = new Loader();
                $loader
                    ->addNamespace('Phalcon\Loader', 1234);
            }
        );
    }
}
