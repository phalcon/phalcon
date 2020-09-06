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

use Example\Namespaces\Adapter\Another;
use Example\Namespaces\Adapter\Mongo;
use Phalcon\Autoload\Loader;
use Phalcon\Tests\Fixtures\Traits\LoaderTrait;
use UnitTester;

use function dataDir;

class AutoloadCest
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = classes
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadClasses(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - classes');

        $loader = new Loader();

        $loader
            ->addClass(
                'One',
                dataDir('fixtures/Loader/Example/Classes/One.php')
            )
            ->addClass(
                'Two',
                dataDir('fixtures/Loader/Example/Classes/Two.php')
            )
        ;

        $loader->autoload('One');

        $I->assertEquals(
            [
                'Loading: One',
                'Class: load: ' . dataDir('fixtures/Loader/Example/Classes/One.php'),
            ],
            $loader->getDebug()
        );

        $loader->autoload('Two');

        $I->assertEquals(
            [
                'Loading: Two',
                'Class: load: ' . dataDir('fixtures/Loader/Example/Classes/Two.php'),
            ],
            $loader->getDebug()
        );

        $loader->autoload('Three');

        $I->assertEquals(
            [
                'Loading: Three',
                'Class: 404 : Three',
                'Namespace: 404 : Three',
            ],
            $loader->getDebug()
        );
    }

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = namespaces
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadNamespaces(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - namespaces');

        $loader = new Loader();

        $loader
            ->addNamespace(
                'Example\Namespaces\Base',
                dataDir('fixtures/Loader/Example/Namespaces/Base/')
            )
            ->addNamespace(
                'Example\Namespaces\Adapter',
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/')
            )
            ->addNamespace(
                'Example\Namespaces',
                dataDir('fixtures/Loader/Example/Namespaces/')
            )
        ;

        $loader->autoload(Mongo::class);

        $I->assertEquals(
            [
                'Loading: Example\Namespaces\Adapter\Mongo',
                'Class: 404 : Example\Namespaces\Adapter\Mongo',
                'Namespace: Example\Namespaces\Adapter\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/') .
                'Mongo.php',
            ],
            $loader->getDebug()
        );
    }

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = namespaces multiple
     * folders
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadNamespacesMultipleFolders(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - namespaces multiple folders');

        $loader = new Loader();

        $loader
            ->addNamespace(
                'Example\Namespaces\Base',
                dataDir('fixtures/Loader/Example/Namespaces/Base/')
            )
            ->addNamespace(
                'Example\Namespaces\Adapter',
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/')
            )
            ->addNamespace(
                'Example\Namespaces',
                dataDir('fixtures/Loader/Example/Namespaces/')
            )
        ;
        $loader
            ->setNamespaces(
                [
                    'Example\Namespaces\Adapter' => [
                        dataDir('fixtures/Loader/Example/Namespaces/Adapter/'),
                        dataDir('fixtures/Loader/Example/Namespaces/Plugin/'),
                    ],
                ]
            );

        $loader->autoload(Another::class);

        $I->assertEquals(
            [
                'Loading: Example\Namespaces\Adapter\Another',
                'Class: 404 : Example\Namespaces\Adapter\Another',
                'Load: 404 : Example\Namespaces\Adapter\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/Another.php'),
                'Namespace: Example\Namespaces\Adapter\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Plugin/Another.php'),
            ],
            $loader->getDebug()
        );
    }

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = namespaces no folders
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadNamespacesNoFolders(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - namespaces no folders');

        $loader = new Loader();

        $loader->autoload(Mongo::class);

        $I->assertEquals(
            [
                'Loading: Example\Namespaces\Adapter\Mongo',
                'Class: 404 : Example\Namespaces\Adapter\Mongo',
                'Load: No folders registered: Example\Namespaces\Adapter\\',
                'Load: No folders registered: Example\Namespaces\\',
                'Load: No folders registered: Example\\',
                'Namespace: 404 : Example\Namespaces\Adapter\Mongo',
            ],
            $loader->getDebug()
        );
    }

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = namespaces 404
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadNamespaces404(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - namespaces 404');

        $loader = new Loader();

        $loader
            ->addNamespace(
                'Example\Namespaces\Adapter',
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/')
            );

        $loader->autoload('Example\Namespaces\Adapter\Unknown');

        $I->assertEquals(
            [
                'Loading: Example\Namespaces\Adapter\Unknown',
                'Class: 404 : Example\Namespaces\Adapter\Unknown',
                'Load: 404 : Example\Namespaces\Adapter\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Adapter/Unknown.php'),
                'Load: No folders registered: Example\Namespaces\\',
                'Load: No folders registered: Example\\',
                'Namespace: 404 : Example\Namespaces\Adapter\Unknown',
            ],
            $loader->getDebug()
        );
    }

    /**
     * Tests Phalcon\Autoloader\Loader :: autoload() = extension
     *
     * @since  2018-11-13
     */
    public function autoloaderLoaderAutoloadExtension(UnitTester $I)
    {
        $I->wantToTest('Autoload\Loader - autoload() - extension');

        $loader = new Loader();

        $loader
            ->setExtensions(
                [
                    'inc',
                ]
            )
            ->setNamespaces(
                [
                    'Example\Namespaces\Base' => dataDir('fixtures/Loader/Example/Namespaces/Base/'),
                    'Example\Namespaces'      => dataDir('fixtures/Loader/Example/Namespaces/'),
                    'Example'                 => dataDir('fixtures/Loader/Example/Namespaces/'),
                ]
            )
        ;

        $loader->autoload('Example\Namespaces\Engines\Alcohol');

        $I->assertEquals(
            [
                'Loading: Example\Namespaces\Engines\Alcohol',
                'Class: 404 : Example\Namespaces\Engines\Alcohol',
                'Load: No folders registered: Example\Namespaces\Engines\\',
                'Load: 404 : Example\Namespaces\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Engines/Alcohol.php'),
                'Namespace: Example\Namespaces\ - ' .
                dataDir('fixtures/Loader/Example/Namespaces/Engines/Alcohol.inc'),
            ],
            $loader->getDebug()
        );
    }
}
