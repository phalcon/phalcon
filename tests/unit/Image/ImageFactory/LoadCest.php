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

namespace Phalcon\Tests\Unit\Image\ImageFactory;

use Phalcon\Image\Adapter\Imagick;
use Phalcon\Image\ImageFactory;
use Phalcon\Tests\Fixtures\Traits\FactoryTrait;
use UnitTester;

class LoadCest
{
    use FactoryTrait;

    public function _before(UnitTester $I)
    {
        $I->checkExtensionIsLoaded('imagick');

        $this->init();
    }

    /**
     * Tests Phalcon\Image\ImageFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function imageImageFactoryLoad(UnitTester $I)
    {
        $I->wantToTest('Image\ImageFactory - load()');

        $options = $this->config->image;
        $factory = new ImageFactory();

        /** @var Imagick $image */
        $image = $factory->load($options);

        $class = Imagick::class;
        $I->assertInstanceOf($class, $image);

        $expected = realpath($options->file);
        $actual   = $image->getRealpath();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Image\ImageFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function imageImageFactoryLoadArray(UnitTester $I)
    {
        $I->wantToTest('Image\ImageFactory - load()');

        $options = $this->arrayConfig['image'];
        $factory = new ImageFactory();

        /** @var Imagick $image */
        $image = $factory->load($options);

        $class = Imagick::class;
        $I->assertInstanceOf($class, $image);

        $expected = realpath($options['file']);
        $actual   = $image->getRealpath();
        $I->assertSame($expected, $actual);
    }
}
