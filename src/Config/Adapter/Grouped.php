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

namespace Phalcon\Config\Adapter;

use Phalcon\Config\Config;
use Phalcon\Config\ConfigFactory;
use Phalcon\Config\ConfigInterface;
use Phalcon\Config\Exception;

use function is_array;
use function is_string;
use function is_object;

/**
 * Reads multiple files (or arrays) and merges them all together.
 *
 * See `Phalcon\Config\Factory::load` To load Config Adapter class using 'adapter' option.
 *
 * ```php
 * use Phalcon\Config\Adapter\Grouped;
 *
 * $config = new Grouped(
 *     [
 *         "path/to/config.php",
 *         "path/to/config.dist.php",
 *     ]
 * );
 * ```
 *
 * ```php
 * use Phalcon\Config\Adapter\Grouped;
 *
 * $config = new Grouped(
 *     [
 *         "path/to/config.json",
 *         "path/to/config.dist.json",
 *     ],
 *     "json"
 * );
 * ```
 *
 * ```php
 * use Phalcon\Config\Adapter\Grouped;
 *
 * $config = new Grouped(
 *     [
 *         [
 *             "filePath" => "path/to/config.php",
 *             "adapter"  => "php",
 *         ],
 *         [
 *             "filePath" => "path/to/config.json",
 *             "adapter"  => "json",
 *         ],
 *         [
 *             "adapter"  => "array",
 *             "config"   => [
 *                 "property" => "value",
 *             ],
 *         ],
 *     ],
 * );
 * ```
 */
class Grouped extends Config
{
    /**
     * Grouped constructor.
     *
     * @param array  $arrayConfig
     * @param string $defaultAdapter
     *
     * @throws Exception
     */
    public function __construct(array $arrayConfig, string $defaultAdapter = 'php')
    {
        parent::__construct([]);

        foreach ($arrayConfig as $configName) {
            $configInstance = $configName;

            // Set to default adapter if passed as string
            if (is_object($configName) && $configName instanceof ConfigInterface) {
                $this->merge($configInstance);
                continue;
            } elseif (is_string($configName)) {
                if ('' === $defaultAdapter) {
                    $this->merge(
                        (new ConfigFactory())->load($configName)
                    );

                    continue;
                }

                $configInstance = [
                    'filePath' => $configName,
                    'adapter'  => $defaultAdapter
                ];
            } elseif (false === isset($configInstance['adapter'])) {
                $configInstance['adapter'] = $defaultAdapter;
            }

            if (is_array($configInstance['adapter'])) {
                if (false === isset($configInstance['config'])) {
                    throw new Exception(
                        "To use 'array' adapter you have to specify " .
                        "the 'config' as an array."
                    );
                }

                $configArray    = $configInstance['config'];
                $configInstance = new Config($configArray);
            } else {
                $configInstance = (new ConfigFactory())->load($configInstance);
            }

            $this->merge($configInstance);
        }
    }
}
