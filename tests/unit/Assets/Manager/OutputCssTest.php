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

use Phalcon\Assets\Asset\Css;
use Phalcon\Assets\Collection;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\Fixtures\Assets\TrimFilter;
use Phalcon\Tests\Fixtures\Assets\UppercaseFilter;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use function file_get_contents;
use function outputDir;

use const PHP_EOL;

final class OutputCssTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('escaper');
        $this->setDiService('url');
    }

    public function tearDown(): void
    {
        $this->resetDi();
    }

    /**
     * Tests Phalcon\Assets\Manager :: outputCss() - filter chain custom filter
     * with cssmin
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1198
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2013-09-15
     */
    #[Test]
    public function testAssetsManagerOutputCssFilterChainCustomFilterWithCssmin(): void
    {
        $fileName = $this->getNewFileName('assets_', 'css');
        $fileName = outputDir('tests/assets/' . $fileName);
        $cssFile  = dataDir('assets/assets/1198.css');
        $manager  = new Manager(new TagFactory(new Escaper()));

        $manager->useImplicitOutput(false);

        $css = $manager->collection('css');

        $css
            ->setTargetPath($fileName)
            ->addCss($cssFile)
            ->addFilter(new UppercaseFilter())
            ->addFilter(new TrimFilter())
            ->join(true)
        ;

        $manager->outputCss('css');

        $needle  = 'A{TEXT-DECORATION:NONE;}B{FONT-WEIGHT:BOLD;}';
        $content = file_get_contents($fileName);
        $this->assertStringContainsString(
            $needle,
            $content
        );

        $this->safeDeleteFile($fileName);
    }

    /**
     * Tests Phalcon\Assets\Manager :: outputCss() - implicit
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-13
     */
    #[Test]
    public function testAssetsManagerOutputCssImplicit(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addCss('css/style1.css');
        $manager->addCss('css/style2.css');
        $manager->addAsset(
            new Css('/css/style.css', false)
        );

        $expected = '<link rel="stylesheet" type="text/css" href="/css/style1.css" />' . PHP_EOL
            . '<link rel="stylesheet" type="text/css" href="/css/style2.css" />' . PHP_EOL
            . '<link rel="stylesheet" type="text/css" href="/css/style.css" />' . PHP_EOL;

        $manager->useImplicitOutput(false);

        $this->assertSame($expected, $manager->outputCss());
    }

    /**
     * Tests Phalcon\Assets\Manager :: outputCss() - not implicit
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-13
     */
    #[Test]
    public function testAssetsManagerOutputCssNotImplicit(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addCss('css/style1.css');
        $manager->addCss('css/style2.css');
        $manager->addAsset(new Css('/css/style.css', false));

        $expected = '<link rel="stylesheet" type="text/css" href="/css/style1.css" />' . PHP_EOL
            . '<link rel="stylesheet" type="text/css" href="/css/style2.css" />' . PHP_EOL
            . '<link rel="stylesheet" type="text/css" href="/css/style.css" />' . PHP_EOL;

        ob_start();
        $manager->outputCss();
        $actual = ob_get_clean();

        $this->assertSame($expected, $actual);
    }
}
