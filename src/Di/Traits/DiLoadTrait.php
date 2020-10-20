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

namespace Phalcon\Di\Traits;

use Phalcon\Config\ConfigInterface;

/**
 * Trait DiLoadTrait
 *
 * @package Phalcon\Di\Traits
 */
trait DiLoadTrait
{
    /**
     * Loads services from a Config object.
     */
    protected function loadFromConfig(ConfigInterface $config): void
    {
        $services = $config->toArray();

        foreach ($services as $name => $service) {
            $this->set(
                $name,
                $service,
                (bool) ($service['shared'] ?? false)
            );
        }
    }

    /**
     * Loads services from a php config file.
     *
     * ```php
     * $di->loadFromPhp("path/services.php");
     * ```
     *
     * And the services can be specified in the file as:
     *
     * ```php
     * return [
     *      'myComponent' => [
     *          'className' => '\Acme\Components\MyComponent',
     *          'shared' => true,
     *      ],
     *      'group' => [
     *          'className' => '\Acme\Group',
     *          'arguments' => [
     *              [
     *                  'type' => 'service',
     *                  'service' => 'myComponent',
     *              ],
     *          ],
     *      ],
     *      'user' => [
     *          'className' => '\Acme\User',
     *      ],
     * ];
     * ```
     *
     * @link https://docs.phalcon.io/en/latest/di
     */
    public function loadFromPhp(string $filePath): void
    {
        // $services = new Php(filePath);
        // $this->loadFromConfig($services);
        echo $filePath;
    }

    /**
     * Loads services from a yaml file.
     *
     * ```php
     * $di->loadFromYaml(
     *     "path/services.yaml",
     *     [
     *         "!approot" => function ($value) {
     *             return dirname(__DIR__) . $value;
     *         }
     *     ]
     * );
     * ```
     *
     * And the services can be specified in the file as:
     *
     * ```php
     * myComponent:
     *     className: \Acme\Components\MyComponent
     *     shared: true
     *
     * group:
     *     className: \Acme\Group
     *     arguments:
     *         - type: service
     *           name: myComponent
     *
     * user:
     *    className: \Acme\User
     * ```
     *
     * @link https://docs.phalcon.io/en/latest/di
     */
    public function loadFromYaml(string $filePath, array $callbacks = null): void
    {
        // $services = new Yaml($filePath, $callbacks);
        // $this->loadFromConfig($services);
        echo $filePath;
        echo serialize($callbacks);
    }
}
