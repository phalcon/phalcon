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
use Phalcon\Tests\AbstractUnitTestCase;

use function hash;

final class GetAddSetNamespacesTest extends AbstractUnitTestCase
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader ::
     * getNamespaces()/addNamespace()/setNamespace()
     *
     * @return void
     *
     * @throws Exception
     * @since  2020-09-09
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testAutoloaderLoaderGetAddSetNamespaces(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $loader = new Loader();

        $expected = [];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);

        $loader->setNamespaces(
            [
                'Phalcon\Loader'   => '/path/to/loader',
                'Phalcon\Provider' => [
                    '/path/to/provider/source',
                    '/path/to/provider/target',
                ],
            ]
        );

        $expected = [
            'Phalcon\Loader\\'   => [
                hash("sha256", '/path/to/loader/') => '/path/to/loader/',
            ],
            'Phalcon\Provider\\' => [
                hash("sha256", '/path/to/provider/source/') => '/path/to/provider/source/',
                hash("sha256", '/path/to/provider/target/') => '/path/to/provider/target/',
            ],
        ];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);

        /**
         * Clear
         */
        $loader->setNamespaces([]);

        $expected = [];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);

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

        $expected = [
            'Phalcon\Loader\\'   => [
                hash("sha256", '/path/to/loader/') => '/path/to/loader/',
            ],
            'Phalcon\Provider\\' => [
                hash("sha256", '/path/to/provider/source/') => '/path/to/provider/source/',
                hash("sha256", '/path/to/provider/target/') => '/path/to/provider/target/',
            ],
        ];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);

        /**
         * Clear - prepend
         */
        $loader->setNamespaces([]);

        $expected = [];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);

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

        $expected = [
            'Phalcon\Loader\\' => [
                hash("sha256", '/path/to/provider/target/') => '/path/to/provider/target/',
                hash("sha256", '/path/to/loader/')          => '/path/to/loader/',
                hash("sha256", '/path/to/provider/source/') => '/path/to/provider/source/',
            ],
        ];
        $actual   = $loader->getNamespaces();
        $this->assertSame($expected, $actual);
    }
}
