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
use Phalcon\Tests\UnitTestCase;

final class HasTest extends UnitTestCase
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: exists()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextHas(): void
    {
        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $actual = $translator->has('hi');
        $this->assertTrue($actual);
    }
}
