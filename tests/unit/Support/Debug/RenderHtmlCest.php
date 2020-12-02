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

namespace Phalcon\Tests\Unit\Support\Debug;

use Phalcon\Support\Debug\Debug;
use Phalcon\Support\Exception;
use Phalcon\Version\Version;
use UnitTester;

/**
 * Class RenderHtmlCest
 *
 * @package Phalcon\Tests\Unit\Support\Debug
 */
class RenderHtmlCest
{
    /**
     * Tests Phalcon\Debug :: renderHtml()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function debugRenderHtml(UnitTester $I)
    {
        $I->wantToTest('Debug - renderHtml()');

        $exception = new Exception('exception message', 1234);
        $debug     = new Debug();
        $debug->setShowBackTrace(false);

        $version = Version::get();
        $link    = Version::getPart(Version::VERSION_MAJOR)
            . "."
            . Version::getPart(Version::VERSION_MEDIUM);


        $expected = '<html><head>'
            . '<title>Phalcon\Support\Exception: exception message</title>'
            . '<link rel="stylesheet" type="text/css" '
            . 'href="https://assets.phalcon.io/debug/4.0.x/bower_components/'
            . 'jquery-ui/themes/ui-lightness/jquery-ui.min.css" />'
            . '<link rel="stylesheet" type="text/css" '
            . 'href="https://assets.phalcon.io/debug/4.0.x/bower_components/'
            . 'jquery-ui/themes/ui-lightness/theme.css" />'
            . '<link rel="stylesheet" type="text/css" '
            . 'href="https://assets.phalcon.io/debug/4.0.x/themes/default/style.css" />'
            . '</head><body>'
            . '<div class="version">Phalcon Framework '
            . '<a href="https://docs.phalcon.io/' . $link . '/en/" '
            . 'target="_new">' . $version . '</a>'
            . '</div>'
            . '<div align="center">'
            . '<div class="error-main">'
            . '<h1>Phalcon\Support\Exception: exception message</h1>'
            . '<span class="error-file">'
            . __FILE__
            . ' (41)</span>'
            . '</div>'
            . '<script type="text/javascript" '
            . 'src="https://assets.phalcon.io/debug/4.0.x/bower_components/'
            . 'jquery/dist/jquery.min.js"></script>'
            . '<script type="text/javascript" '
            . 'src="https://assets.phalcon.io/debug/4.0.x/bower_components/'
            . 'jquery-ui/jquery-ui.min.js"></script>'
            . '<script type="text/javascript" '
            . 'src="https://assets.phalcon.io/debug/4.0.x/bower_components/'
            . 'jquery.scrollTo/jquery.scrollTo.min.js"></script>'
            . '<script type="text/javascript" '
            . 'src="https://assets.phalcon.io/debug/4.0.x/prettify/prettify.js"></script>'
            . '<script type="text/javascript" '
            . 'src="https://assets.phalcon.io/debug/4.0.x/pretty.js"></script>'
            . '</div>'
            . '</body></html>';

        $actual   = $debug->renderHtml($exception);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Debug :: renderHtml() - with backtrace
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function debugRenderHtmlWithBacktrace(UnitTester $I)
    {
        $I->wantToTest('Debug - renderHtml() - with backtrace');

        $exception = new Exception('exception message', 1234);
        $debug     = new Debug();
        $debug->setShowBackTrace(true);

        $actual   = $debug->renderHtml($exception);
        $I->assertStringContainsString(
            '<div class="error-info">',
            $actual
        );
        $I->assertStringContainsString(
            '<li><a href="#error-tabs-1">Backtrace</a></li>',
            $actual
        );
        $I->assertStringContainsString(
            '<li><a href="#error-tabs-2">Request</a></li>',
            $actual
        );
        $I->assertStringContainsString(
            '<li><a href="#error-tabs-3">Server</a></li>',
            $actual
        );
        $I->assertStringContainsString(
            '<li><a href="#error-tabs-4">Included Files</a></li>',
            $actual
        );
        $I->assertStringContainsString(
            '<li><a href="#error-tabs-5">Memory</a></li>',
            $actual
        );
    }
}
