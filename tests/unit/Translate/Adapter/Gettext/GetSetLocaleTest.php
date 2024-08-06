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

final class GetSetLocaleTest extends UnitTestCase
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: getLocale()/setLocale()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextGetSetLocale(): void
    {
        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $expected = 'en_US.utf8';
        $actual   = $translator->getLocale();
        $this->assertSame($expected, $actual);

        $translator->setLocale(1, ['ru']);

        $expected = '';
        $actual   = $translator->getLocale();
        $this->assertEquals($expected, $actual);

        $translator->setLocale(1, ['ru_RU.utf8']);

        $expected = 'ru_RU.utf8';
        $actual   = $translator->getLocale();
        $this->assertSame($expected, $actual);
    }
}
