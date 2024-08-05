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
use Phalcon\Tests\Fixtures\Translate\Adapter\NativeAdapterFixture;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;

final class NotFoundTest extends UnitTestCase
{
    use TranslateNativeArrayTrait;

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: notFound() - default
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativearrayNotFound(): void
    {
        $language = $this->getArrayConfig()['en'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $expected = 'unknown';
        $actual   = $translator->query($expected);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: notFound() - custom
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativearrayNotFoundCustom(): void
    {
        $language = $this->getArrayConfig()['en'];

        $translator = new NativeAdapterFixture(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $expected = '';
        $actual   = $translator->query('unknown');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: notFound() - triggerError
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativearrayNotFoundTriggerError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot find translation key: unknown');

        $language = $this->getArrayConfig()['en'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content'      => $language,
                'triggerError' => true,
            ]
        );

        $translator->query('unknown');
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: notFound() - triggerError
     * random value
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativearrayNotFoundTriggerErrorRandomVaue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot find translation key: unknown');

        $language = $this->getArrayConfig()['en'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content'      => $language,
                'triggerError' => 'blahblah',
            ]
        );

        $translator->query('unknown');
    }
}
