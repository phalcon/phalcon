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

use function dataDir;
use function function_exists;

final class GetFoundPathTest extends AbstractUnitTestCase
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader :: getFoundPath()
     *
     * @return void
     *
     * @throws Exception
     * @since  2020-09-09
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testAutoloaderLoaderGetFoundPath(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $loader = new Loader();
        $file   = dataDir('fixtures/Loader/Example/Functions/FunctionsNoClass.php');
        $loader->addFile($file);

        $loader->loadFiles();

        $actual = function_exists('noClassFoo');
        $this->assertTrue($actual);

        $expected = $file;
        $actual   = $loader->getFoundPath();
        $this->assertSame($expected, $actual);
    }
}
