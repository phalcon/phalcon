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
use Phalcon\Tests\UnitTestCase;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\InterpolatorFactory;

final class SetDomainTest extends UnitTestCase
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: setDomain()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextSetDomain(): void
    {
        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $this->assertSame('Hello', $translator->_('hi'));

        //Check with a domain which doesn't exist
        $translator->setDomain('no_exist');
        $this->assertSame('hi', $translator->_('hi'));

        //Put the good one
        $translator->setDomain('messages');
        $this->assertSame('Hello', $translator->_('hi'));
    }
}
