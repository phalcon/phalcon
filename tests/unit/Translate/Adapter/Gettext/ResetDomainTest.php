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
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('gettext')]
final class ResetDomainTest extends AbstractUnitTestCase
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: resetDomain()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterGettextResetDomain(): void
    {
        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        //Put the good one to get the return textdomain
        $oTextDomainMessage = $translator->setDomain('messages');
        $this->assertSame('Hello', $translator->_('hi'));

        //Check with a domain which doesn't exist
        $oTextDomainNoExist = $translator->setDomain('no_exist');
        $this->assertSame('hi', $translator->_('hi'));

        $oTextDomainReset = $translator->resetDomain();
        $this->assertSame('Hello', $translator->_('hi'));
        $this->assertNotEquals($oTextDomainNoExist, $oTextDomainReset);
        $this->assertSame($oTextDomainMessage, $oTextDomainReset);
    }
}
