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
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\Test;

final class OffsetUnsetTest extends AbstractUnitTestCase
{
    use TranslateCsvTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Csv :: offsetUnset()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterCsvOffsetUnset(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Translate is an immutable ArrayAccess object'
        );

        $language   = $this->getCsvConfig()['en'];
        $translator = new Csv(new InterpolatorFactory(), $language);
        $translator->offsetUnset('hi');
    }
}
