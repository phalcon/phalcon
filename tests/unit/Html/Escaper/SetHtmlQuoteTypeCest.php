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
 * Class SetHtmlQuoteTypeCest
 *
 * @package Phalcon\Tests\Unit\Html\Escaper
 */
class SetHtmlQuoteTypeCest
{
    /**
     * Tests Phalcon\Escaper :: setHtmlQuoteType()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function escaperSetHtmlQuoteType(UnitTester $I)
    {
        $I->wantToTest('Escaper - setHtmlQuoteType()');

        $escaper = new Escaper();

        $escaper->setHtmlQuoteType(ENT_HTML401);

        $I->assertEquals(
            'That&#039;s right',
            $escaper->escapeHtmlAttr("That's right")
        );
    }
}
