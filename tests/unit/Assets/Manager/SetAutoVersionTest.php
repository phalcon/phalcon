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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use function dataDir;
use function filemtime;

final class SetAutoVersionTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * executed before each test
     */
    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');
    }

    /**
     * Tests Phalcon\Assets\Manager :: addJs() - automatic versioning
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2017-02-01
     */
    #[Test]
    public function testAssetsManagerAddJsAutomaticVersioning(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $manager = new Manager(new TagFactory(new Escaper()));
        $manager->useImplicitOutput(false);
        $manager->addJs(
            dataDir('assets/assets/assets-version-1.js'),
            true,
            false,
            [],
            '1.0.0'
        );

        $manager->addJs(
            dataDir('assets/assets/assets-version-2.js'),
            true,
            false,
            [],
            null,
            true
        );

        $manager->addJs(
            dataDir('assets/assets/assets-version-3.js'),
            true,
            false,
            [],
            null,
            false
        );

        $pathData         = dataDir('assets/');
        $modificationTime = filemtime(
            dataDir('assets/assets/assets-version-3.js')
        );

        $expected = sprintf(
            "%s" . PHP_EOL . "%s" . PHP_EOL . "%s" . PHP_EOL,
            "<script type=\"application/javascript\" " .
            "src=\"{$pathData}assets/assets-version-1.js?ver=1.0.0\"></script>",
            "<script type=\"application/javascript\" " .
            "src=\"{$pathData}assets/assets-version-2.js?ver=$modificationTime\"></script>",
            "<script type=\"application/javascript\" " .
            "src=\"{$pathData}assets/assets-version-3.js\"></script>"
        );
        $this->assertSame($expected, $manager->outputJs());
    }
}
