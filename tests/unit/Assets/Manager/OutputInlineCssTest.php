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
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

use const PHP_EOL;

final class OutputInlineCssTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: outputInlineCss()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testAssetsManagerOutputInlineCss(): void
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
}
