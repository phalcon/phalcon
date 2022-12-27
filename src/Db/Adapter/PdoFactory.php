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

namespace Phalcon\Db\Adapter;

use Phalcon\Config\Config;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Postgresql;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;

class PdoFactory
{
    use ConfigTrait;
    use FactoryTrait;

    /**
     * Constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|Config $config = [
     *                             'adapter' => 'mysql',
     *                             'options' => [
     *                             'host' => 'localhost',
     *                             'port' => '3306',
     *                             'dbname' => 'blog',
     *                             'username' => 'sigma'
     *                             'password' => 'secret',
     *                             'dialectClass' => null,
     *                             'options' => [],
     *                             'dsn' => null,
     *                             'charset' => 'utf8mb4'
     *                             ]
     *                             ]
     */
    public function load(Config|array $config): AdapterInterface
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");
        $name   = $config["adapter"];

        unset($config["adapter"]);

        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array  $options
     *
     * @return AdapterInterface
     */
    public function newInstance(
        string $name,
        array $options = []
    ): AdapterInterface {
        $definition = $this->getService($name);

        return new $definition($options);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return "Phalcon\\Db\\Exception";
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "mysql"      => Mysql::class,
            "postgresql" => Postgresql::class,
            "sqlite"     => Sqlite::class,
        ];
    }
}
