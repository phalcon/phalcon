<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Image\ImageFactory;

use Phalcon\Image\Adapter\Imagick;
use Phalcon\Image\Exception;
use Phalcon\Image\ImageFactory;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use Phalcon\Tests\Support\Traits\FactoryTrait;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use TypeError;

use function uniqid;

#[RequiresPhpExtension('imagick')]
final class LoadTest extends AbstractUnitTestCase
{
    use FactoryTrait;

    public function setUp(): void
    {
        $this->init();
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testImageImageFactoryLoad(): void
    {
        $options = $this->config->image;
        $factory = new ImageFactory();

        /** @var Imagick $image */
        $image = $factory->load($options);

        $class = Imagick::class;
        $this->assertInstanceOf($class, $image);

        $expected = realpath($options->file);
        $actual   = $image->getRealpath();
        $this->assertSame($expected, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testImageImageFactoryLoadArray(): void
    {
        $options = $this->arrayConfig['image'];
        $factory = new ImageFactory();

        /** @var Imagick $image */
        $image = $factory->load($options);

        $class = Imagick::class;
        $this->assertInstanceOf($class, $image);

        $expected = realpath($options['file']);
        $actual   = $image->getRealpath();
        $this->assertSame($expected, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-08-05
     */
    public function testImageImageFactoryLoadException(): void
    {
        $factory = new ImageFactory();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "You must provide the 'adapter' option in the factory config parameter."
        );

        $factory->load([]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-08-05
     */
    public function testImageImageFactoryLoadExceptionInvalidConfig(): void
    {
        $factory = new ImageFactory();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Config must be array or Phalcon\Config\Config object'
        );

        $factory->load(1234);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/873
     */
    public function testImageImageFactoryLoadSizeNotNumeric(): void
    {
        $factory = new ImageFactory();

        $this->expectException(TypeError::class);

        $factory->load(
            [
                'adapter' => 'imagick',
                'file'    => Talon::settings()->outputPath() . '/' . uniqid('image-') . '.png',
                'width'   => 'abc',
                'height'  => '20',
            ]
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/873
     */
    public function testImageImageFactoryLoadSizeNumericString(): void
    {
        $factory = new ImageFactory();

        $image = $factory->load(
            [
                'adapter' => 'imagick',
                'file'    => Talon::settings()->outputPath() . '/' . uniqid('image-') . '.png',
                'width'   => '10',
                'height'  => '20',
            ]
        );

        $this->assertSame(10, $image->getWidth());
        $this->assertSame(20, $image->getHeight());
    }
}
