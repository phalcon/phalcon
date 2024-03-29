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

/**
 * Class SetDomainCest
 *
 * @package Phalcon\Tests\Unit\Translate\Adapter\Gettext
 */
class SetDomainCest
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: setDomain()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateAdapterGettextSetDomain(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\Gettext - setDomain()');

        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $I->assertSame('Hello', $translator->_('hi'));

        //Check with a domain which doesn't exist
        $translator->setDomain('no_exist');
        $I->assertSame('hi', $translator->_('hi'));

        //Put the good one
        $translator->setDomain('messages');
        $I->assertSame('Hello', $translator->_('hi'));
    }
}
