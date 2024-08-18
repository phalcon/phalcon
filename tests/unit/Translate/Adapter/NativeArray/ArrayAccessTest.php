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

namespace Phalcon\Tests\Unit\Translate\Adapter\NativeArray;

use Phalcon\Tests\Fixtures\Traits\TranslateNativeArrayTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use PHPUnit\Framework\Attributes\Test;

final class ArrayAccessTest extends AbstractUnitTestCase
{
    use TranslateNativeArrayTrait;

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: array access
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testTranslateAdapterNativeArrayWithArrayAccess(): void
    {
        $language = $this->getArrayConfig()['ru'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $actual = isset($translator['Hello!']);
        $this->assertTrue($actual);

        $actual = isset($translator['Hi there!']);
        $this->assertFalse($actual);

        $expected = $language['Hello!'];
        $actual   = $translator['Hello!'];
        $this->assertSame($expected, $actual);
    }
}
