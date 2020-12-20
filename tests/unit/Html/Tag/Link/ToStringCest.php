<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Tag\Link;

use Phalcon\Html\Escaper;
use Phalcon\Html\Tag\Link;
use UnitTester;

/**
 * Class ToStringCest
 *
 * @package Phalcon\Tests\Unit\Html\Tag\Link
 */
class ToStringCest
{
    /**
     * Tests Phalcon\Html\Tag\Link :: __toString() - empty
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function htmlHelperLinkToStringEmpty(UnitTester $I)
    {
        $I->wantToTest('Html\Tag\Link - __toString() - empty');

        $escaper = new Escaper();
        $helper  = new Link($escaper);

        $result = $helper();

        $actual = (string) $result;
        $I->assertEmpty($actual);
    }
}
