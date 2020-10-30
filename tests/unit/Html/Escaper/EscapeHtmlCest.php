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

namespace Phalcon\Tests\Unit\Html\Escaper;

use Phalcon\Html\Escaper;
use UnitTester;

/**
 * Class EscapeHtmlCest
 *
 * @package Phalcon\Tests\Unit\Html\Escaper
 */
class EscapeHtmlCest
{
    /**
     * Tests Phalcon\Escaper :: escapeHtml()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function escaperEscapeHtml(UnitTester $I)
    {
        $I->wantToTest('Escaper - escapeHtml()');

        $escaper = new Escaper();

        $I->assertEquals(
            '&lt;h1&gt;&lt;/h1&gt;',
            $escaper->escapeHtml('<h1></h1>')
        );
    }

    /**
     * Tests Phalcon\Escaper :: escapeHtml() - null
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function escaperEscapeHtmlNull(UnitTester $I)
    {
        $I->wantToTest('Escaper - escapeHtml() - null');

        $escaper = new Escaper();

        $I->assertEquals(
            '',
            $escaper->escapeHtml(null)
        );
    }
}
