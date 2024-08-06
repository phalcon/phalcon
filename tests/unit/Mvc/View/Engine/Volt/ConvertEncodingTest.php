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

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Tests\UnitTestCase;

use function chr;

class ConvertEncodingTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt :: convertEncoding()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-13
     */
    public function testMvcViewEngineVoltConvertEncoding(): void
    {
        $view   = new View();
        $engine = new Volt($view);

        $text     = 'Schlüssel';
        $from     = 'latin1';
        $to       = 'utf8';
        $expected = 'Schl' . chr(252) . 'ssel';
        $actual   = $engine->convertEncoding($text, $from, $to);
        $this->assertEquals($expected, $actual);

        $text     = 'Schl' . chr(252) . 'ssel';
        $from     = 'utf8';
        $to       = 'latin1';
        $expected = 'Schl' . chr(195) . chr(188) . 'ssel';
        $actual   = $engine->convertEncoding($text, $from, $to);
        $this->assertEquals($expected, $actual);

        $text     = 'Schlüssel';
        $from     = 'utf7';
        $to       = 'euc-jp';
        $expected = 'Schl+eu8-ssel';
        $actual   = $engine->convertEncoding($text, $from, $to);
        $this->assertEquals($expected, $actual);
    }
}
