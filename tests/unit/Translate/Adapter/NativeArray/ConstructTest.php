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

use ArrayAccess;
use Phalcon\Tests\Fixtures\Traits\TranslateNativeArrayTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;

final class ConstructTest extends AbstractUnitTestCase
{
    use TranslateNativeArrayTrait;

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativeArrayConstruct(): void
    {
        $language = $this->getArrayConfig()['en'];

        $translator = new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => $language,
            ]
        );

        $this->assertInstanceOf(
            ArrayAccess::class,
            $translator
        );

        $this->assertInstanceOf(
            AdapterInterface::class,
            $translator
        );
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: __construct() - Exception
     * content not array
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativeArrayContentNotArray(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Translation data must be an array');

        (new NativeArray(
            new InterpolatorFactory(),
            [
                'content' => 1234,
            ]
        ));
    }

    /**
     * Tests Phalcon\Translate\Adapter\NativeArray :: __construct() - Exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterNativeArrayContentParamExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Translation content was not provided');

        (new NativeArray(new InterpolatorFactory(), []));
    }
}
