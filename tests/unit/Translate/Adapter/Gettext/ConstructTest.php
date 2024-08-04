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

use ArrayAccess;
use Codeception\Stub;
use Phalcon\Tests\Fixtures\Traits\TranslateGettextTrait;
use Phalcon\Tests\Fixtures\Translate\Adapter\GettextFileExistsFixture;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextConstruct(): void
    {
        $params = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $this->assertInstanceOf(ArrayAccess::class, $translator);
        $this->assertInstanceOf(AdapterInterface::class, $translator);
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct() - Exception
     * 'locale' not passed in options
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextContentParamLocaleExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Parameter 'locale' is required");

        (new Gettext(new InterpolatorFactory(), []));
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct() - Exception
     * 'directory' not passed in options
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextContentParamDirectoryExist(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Parameter 'directory' is required");

        (new Gettext(
            new InterpolatorFactory(),
            [
                'locale' => 'en_US.utf8',
            ]
        ));
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct() - Exception
     * no gettext extension loaded
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateAdapterGettextConstructNoGettextException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This class requires the gettext extension for PHP');

        (new GettextFileExistsFixture(
            new InterpolatorFactory(),
            [
                'locale' => 'en_US.utf8',
            ],
        ));
    }
}
