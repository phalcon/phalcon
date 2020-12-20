<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Tag\Meta;

use Phalcon\Html\Escaper;
use Phalcon\Html\Tag\Meta;
use UnitTester;

/**
 * Class ToStringCest
 *
 * @package Phalcon\Tests\Unit\Html\Tag\Meta
 */
class ToStringCest
{
    /**
     * Tests Phalcon\Html\Tag\Meta :: __toString() - empty
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function htmlHelperMetaToStringEmpty(UnitTester $I)
    {
        $I->wantToTest('Html\Tag\Meta - __toString() - empty');

        $escaper = new Escaper();
        $helper  = new Meta($escaper);

        $result = $helper();

        $actual = (string) $result;
        $I->assertEmpty($actual);
    }
}
