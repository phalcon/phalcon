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

namespace Phalcon\Tests\Unit\Translate\TranslateFactory;

use Phalcon\Tests\Fixtures\Traits\FactoryTrait;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Exception as TranslateException;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;
use UnitTester;

/**
 * Class LoadCest
 *
 * @package Phalcon\Tests\Unit\Translate\TranslateFactory
 */
class LoadCest
{
    use FactoryTrait;

    public function _before(UnitTester $I)
    {
        $I->checkExtensionIsLoaded('gettext');

        $this->init();
    }

    /**
     * Tests Phalcon\Translate\Factory :: load() - Phalcon\Config
     *
     * @param UnitTester $I
     *
     * @throws TranslateException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateFactoryLoadConfig(UnitTester $I)
    {
        $I->wantToTest('Translate\Factory - load() - Config');

        $options      = $this->config->translate;
        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $adapter      = $factory->load($options);

        /* https://github.com/phalcon/cphalcon/issues/14764
        /*
         * @todo:ruudboon Remove workaround after bug fix
         */
        $locale = $options->options->locale;
        if (!$adapter->getLocale()) {
            $adapter->setLocale($options->options->category, "en_US");
            $locale = "en_US";
        }

        $I->assertInstanceOf(Gettext::class, $adapter);

        $expected = $options->options->category;
        $actual   = $adapter->getCategory();
        $I->assertEquals($expected, $actual);

        $expected = $locale;
        $actual   = $adapter->getLocale();
        $I->assertEquals($expected, $actual);

        $expected = $options->options->defaultDomain;
        $actual   = $adapter->getDefaultDomain();
        $I->assertEquals($expected, $actual);

        $expected = $options->options->directory;
        $actual   = $adapter->getDirectory();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Translate\Factory :: load() - array
     *
     * @param UnitTester $I
     *
     * @throws TranslateException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateFactoryLoadArray(UnitTester $I)
    {
        $I->wantToTest('Translate\Factory - load() - array');

        $options      = $this->arrayConfig['translate'];
        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $adapter      = $factory->load($options);

        /* https://github.com/phalcon/cphalcon/issues/14764
        /*
         * @todo:ruudboon Remove workaround after bug fix
         */
        $locale = $options['options']['locale'];
        if (!$adapter->getLocale()) {
            $adapter->setLocale($options['options']['category'], "en_US");
            $locale = "en_US";
        }

        $I->assertInstanceOf(
            Gettext::class,
            $adapter
        );

        $expected = $options['options']['category'];
        $actual   = $adapter->getCategory();
        $I->assertEquals($expected, $actual);

        $expected = $locale;
        $actual   = $adapter->getLocale();
        $I->assertEquals($expected, $actual);

        $expected = $options['options']['defaultDomain'];
        $actual   = $adapter->getDefaultDomain();
        $I->assertEquals($expected, $actual);

        $expected = $options['options']['directory'];
        $actual   = $adapter->getDirectory();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Translate\Factory :: load() - exceptions
     *
     * @param UnitTester $I
     *
     * @throws TranslateException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function translateFactoryLoadExceptions(UnitTester $I)
    {
        $I->wantToTest('Translate\Factory - load() - exceptions');

        $options      = $this->arrayConfig['translate'];
        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);

        $I->expectThrowable(
            new TranslateException(
                'Config must be array or Phalcon\Config\Config object'
            ),
            function () use ($factory) {
                $factory->load(1234);
            }
        );

        $I->expectThrowable(
            new TranslateException(
                'You must provide "adapter" option in factory config parameter.'
            ),
            function () use ($factory, $options) {
                $newOptions = $options;
                unset($newOptions['adapter']);

                $factory->load($newOptions);
            }
        );
    }
}
