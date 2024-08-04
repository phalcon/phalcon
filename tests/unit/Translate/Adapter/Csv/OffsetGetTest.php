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

namespace Phalcon\Tests\Unit\Translate\Adapter\Csv;

use Phalcon\Tests\Fixtures\Traits\TranslateCsvTrait;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Tests\UnitTestCase;

final class OffsetGetTest extends UnitTestCase
{
    use TranslateCsvTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Csv :: offsetGet()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterCsvOffsetGet(): void
    {
        $language   = $this->getCsvConfig()['en'];
        $translator = new Csv(new InterpolatorFactory(), $language);

        $expected = 'Hello';
        $actual   = $translator->offsetGet('hi');
        $this->assertSame($expected, $actual);
    }
}
