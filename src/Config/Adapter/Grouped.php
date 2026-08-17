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
use Phalcon\Config\Exceptions\GroupedAdapterRequiresArray;
use Phalcon\Contracts\Config\ConfigTypes;

use function is_string;

/**
 * Reads multiple files (or arrays) and merges them all together.
 *
 * See `Phalcon\Config\ConfigFactory::load` To load Config Adapter class using 'adapter' option.
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
 *
 * @phpstan-import-type config_grouped_entries from ConfigTypes
 * @phpstan-import-type config_options from ConfigTypes
 */
class Grouped extends Config
{
    /**
     * Grouped constructor.
     *
     * @phpstan-param config_grouped_entries $arrayConfig
     *
     * @param string             $defaultAdapter
     * @param ConfigFactory|null $factory        Factory used to load file
     *                                           based fragments; a default
     *                                           one is created when not
     *                                           provided
     */
    public function __construct(
        array $arrayConfig,
        string $defaultAdapter = 'php',
        ConfigFactory | null $factory = null
    ) {
        parent::__construct([]);

        $configFactory = $factory ?? new ConfigFactory();

        foreach ($arrayConfig as $configName) {
            $configInstance = $configName;

            // Set to default adapter if passed as string
            if ($configName instanceof ConfigInterface) {
                /** @var ConfigInterface $configInstance */
                $this->merge($configInstance);

                continue;
            }

            /** @var config_options $configInstance */
            if (is_string($configName)) {
                if ('' === $defaultAdapter) {
                    $this->merge(
                        $configFactory->load($configName)
                    );

                    continue;
                }

                $configInstance = [
                    'filePath' => $configName,
                    'adapter'  => $defaultAdapter,
                ];
            } elseif (!isset($configInstance['adapter'])) {
                $configInstance['adapter'] = $defaultAdapter;
            }

            if ('array' === $configInstance['adapter']) {
                if (!isset($configInstance['config'])) {
                    throw new GroupedAdapterRequiresArray();
                }

                $configArray    = $configInstance['config'];
                $configInstance = new Config($configArray, $this->insensitive);
            } else {
                $configInstance = $configFactory->load($configInstance);
            }

            $this->merge($configInstance);
        }
    }
}
