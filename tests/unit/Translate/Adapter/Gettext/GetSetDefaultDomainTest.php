<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Translate\Adapter\Gettext;

use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Translate\Fake\TranslateGettextTrait;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\InterpolatorFactory;

final class GetSetDefaultDomainTest extends AbstractUnitTestCase
{
    use TranslateGettextTrait;

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextGetSetDefaultDomain(): void
    {

        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $this->assertSame('messages', $translator->getDefaultDomain());

        $translator->setDefaultDomain('options');

        $this->assertSame('options', $translator->getDefaultDomain());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/883
     */
    public function testTranslateAdapterGettextGetSetDefaultDomainInt(): void
    {
        $params                  = $this->getGettextConfig();
        $params['defaultDomain'] = 1;

        $translator = new Gettext(new InterpolatorFactory(), $params);

        $this->assertSame('1', $translator->getDefaultDomain());
    }
}
