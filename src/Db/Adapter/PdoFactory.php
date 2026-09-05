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

use Exception as BaseException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Adapter\Pdo\Postgresql;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Factory\AbstractFactory;
use Phalcon\Support\Exception as SupportException;

/**
 * @phpstan-import-type db_descriptor from DbTypes
 * @phpstan-import-type db_factory_config from DbTypes
 */
class PdoFactory extends AbstractFactory
{
    /**
     * Constructor.
     *
     * @phpstan-param array<string, class-string<AdapterInterface>> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array<string, mixed>|ConfigInterface $config = [
     *                                                     'adapter' => 'mysql',
     *                                                     'options' => [
     *                                                     'host' => 'localhost',
     *                                                     'port' => '3306',
     *                                                     'dbname' => 'blog',
     *                                                     'username' => 'sigma'
     *                                                     'password' => 'secret',
     *                                                     'dialectClass' => null,
     *                                                     'options' => [],
     *                                                     'dsn' => null,
     *                                                     'charset' => 'utf8mb4'
     *                                                     ]
     *                                                     ]
     *
     * @throws SupportException
     * @throws BaseException
     */
    public function load(mixed $config): AdapterInterface
    {
        $config = $this->checkConfig($config);

        /** @phpstan-var db_factory_config $config */
        $config = $this->checkConfigElement($config, "adapter");
        $name   = $config["adapter"];

        unset($config["adapter"]);

        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @phpstan-param db_descriptor $options
     *
     * @throws BaseException
     */
    public function newInstance(
        string $name,
        array $options = []
    ): AdapterInterface {
        /** @var class-string<AdapterInterface> $definition */
        $definition = $this->getService($name);

        return new $definition($options);
    }

    /**
     * @return class-string<\Exception>
     */
    protected function getExceptionClass(): string
    {
        return "Phalcon\\Db\\Exception";
    }

    /**
     * Returns the available adapters
     *
     * @return array<string, string>
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
