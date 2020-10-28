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

namespace Phalcon\Tests\Unit\Translate\Adapter\Gettext;

use Phalcon\Tests\Fixtures\Traits\TranslateGettextTrait;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\InterpolatorFactory;
use UnitTester;

use const LC_ALL;

class GetSetLocaleCest
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: getLocale()/setLocale()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateAdapterGettextGetSetLocale(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\Gettext - getLocale()/setLocale()');

        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $I->assertEquals('en_US.utf8', $translator->getLocale());

        $translator->setLocale(1, 'nl_NL');
        $I->assertFalse($translator->getLocale());

        $translator->setLocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
        $I->assertFalse($translator->getLocale());
    }
}
