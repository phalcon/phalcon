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

use Phalcon\Config\Config;
use Phalcon\Image\Adapter\AdapterInterface;
use Phalcon\Image\Adapter\Gd;
use Phalcon\Image\Adapter\Imagick;
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Factory to create adapters for image manipulation
 */
class ImageFactory
{
    use ConfigTrait;
    use FactoryTrait;

    /**
     * Constructor
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|Config $config                 = [
     *                                             'adapter' => 'gd',
     *                                             'file'    => 'image.jpg',
     *                                             'height'  => null,
     *                                             'width'   => null
     *                                             ]
     *
     * @return AdapterInterface
     */
    public function load($config): AdapterInterface
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");
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
     */
    public function newInstance(
        string $name,
        string $file,
        int $width = null,
        int $height = null
    ): AdapterInterface {
        $definition = $this->getService($name);

        return new $definition($file, $width, $height);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
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
