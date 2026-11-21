<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Autoload;

use Phalcon\Autoload\Loader;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function class_exists;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderAddNamespaceDirectoryInt(): void
    {
        $loader = new Loader();
        $loader->addNamespace('WeakNs887', [123]);

        $this->assertSame(
            ['WeakNs887\\' => ['123/' => '123/']],
            $loader->getNamespaces()
        );

        $loader->register();
        try {
            $this->assertFalse(class_exists('WeakNs887\\Missing'));
        } finally {
            $loader->unregister();
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderSetClassesFileInt(): void
    {
        $loader = new Loader();
        $loader->setClasses(['WeakClass887' => 123]);

        $this->assertSame(['WeakClass887' => '123'], $loader->getClasses());

        $loader->register();
        try {
            $this->assertFalse(class_exists('WeakClass887'));
        } finally {
            $loader->unregister();
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderSetDirectoriesInt(): void
    {
        $loader = new Loader();
        $loader->setDirectories([123]);

        $this->assertSame([123 => '123'], $loader->getDirectories());

        $loader->register();
        try {
            $this->assertFalse(class_exists('WeakDirectory887'));
        } finally {
            $loader->unregister();
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderSetExtensionsInt(): void
    {
        $loader = new Loader();
        $loader->setExtensions([123]);

        $this->assertSame(['php' => 'php', 123 => '123'], $loader->getExtensions());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderSetFilesInt(): void
    {
        $loader = new Loader();
        $loader->setFiles([123]);
        $loader->loadFiles();

        $this->assertSame([123 => '123'], $loader->getFiles());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testAutoloadLoaderSetNamespacesIntName(): void
    {
        $loader = new Loader();
        $loader->setNamespaces(['2024' => '/srv/src']);

        $this->assertSame(
            [
                '2024\\' => [
                    '/srv/src/' => '/srv/src/',
                ],
            ],
            $loader->getNamespaces()
        );
    }
}
