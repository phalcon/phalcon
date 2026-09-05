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

namespace Phalcon\Image;

use Exception as BaseException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Image\ImageTypes;
use Phalcon\Factory\AbstractFactory;
use Phalcon\Image\Adapter\AdapterInterface;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Image\Adapter\Imagick;

/**
 * Factory to create adapters for image manipulation
 *
 * @phpstan-import-type image_factory_config from ImageTypes
 * @phpstan-import-type image_factory_services from ImageTypes
 */
class ImageFactory extends AbstractFactory
{
    /**
     * Constructor
     *
     * @phpstan-param image_factory_services $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @phpstan-param ConfigInterface|image_factory_config $config
     *
     * @param array|ConfigInterface $config = [
     *                                      'adapter' => 'gd',
     *                                      'file' => 'image.jpg',
     *                                      'height' => null,
     *                                      'width' => null
     *                                      ]
     */
    public function load(mixed $config): AdapterInterface
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");

        /** @phpstan-var image_factory_config $config */
        $config = $this->checkConfigElement($config, "file");

        $name = $config["adapter"];

        unset($config["adapter"]);

        $file   = $config["file"];
        $height = $config["height"] ?? null;
        $width  = $config["width"] ?? null;

        return $this->newInstance($name, $file, $width, $height);
    }

    /**
     * Creates a new instance
     *
     * @throws BaseException
     */
    public function newInstance(
        string $name,
        string $file,
        int | null $width = null,
        int | null $height = null
    ): AdapterInterface {
        $definition = $this->getService($name);

        /** @var AdapterInterface $adapter */
        $adapter = new $definition($file, $width, $height);

        return $adapter;
    }

    /**
     * @return class-string<\Exception>
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
     *
     * @phpstan-return image_factory_services
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "gd"      => Gd::class,
            "imagick" => Imagick::class,
        ];
    }
}
