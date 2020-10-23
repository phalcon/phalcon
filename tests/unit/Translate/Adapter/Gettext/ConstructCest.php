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
use Phalcon\Tests\Fixtures\Traits\TranslateGettextTrait;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use UnitTester;

class ConstructCest
{
    use TranslateGettextTrait;

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateAdapterGettextConstruct(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\Gettext - constructor');

        $params     = $this->getGettextConfig();
        $translator = new Gettext(new InterpolatorFactory(), $params);

        $I->assertInstanceOf(ArrayAccess::class, $translator);
        $I->assertInstanceOf(AdapterInterface::class, $translator);
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct() - Exception
     * 'locale' not passed in options
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateAdapterGettextContentParamLocaleExist(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\Gettext - constructor without "locale" throws exception');

        $I->expectThrowable(
            new Exception('Parameter "locale" is required'),
            function () {
                new Gettext(new InterpolatorFactory(), []);
            }
        );
    }

    /**
     * Tests Phalcon\Translate\Adapter\Gettext :: __construct() - Exception
     * 'directory' not passed in options
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateAdapterGettextContentParamDirectoryExist(UnitTester $I)
    {
        $I->wantToTest('Translate\Adapter\Gettext - constructor without "directory" throws exception');

        $I->expectThrowable(
            new Exception('Parameter "directory" is required'),
            function () {
                new Gettext(
                    new InterpolatorFactory(),
                    [
                        'locale' => 'en_US.utf8',
                    ]
                );
            }
        );
    }
}
