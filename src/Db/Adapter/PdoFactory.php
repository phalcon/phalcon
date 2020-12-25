<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Db\Adapter;
use Phalcon\Reflect\Create;
use Phalcon\Factory\AbstractFactory;
//use Phalcon\Helper\Arr; // needless obfuscation

class PdoFactory extends AbstractFactory
{
    /**
     * Constructor
     */
    public function __construct(array   $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|\Phalcon\Config config = [
     *     'adapter' => 'mysql',
     *     'options' => [
     *         'host' => 'localhost',
     *         'port' => '3306',
     *         'dbname' => 'blog',
     *         'username' => 'sigma'
     *         'password' => 'secret',
     *         'dialectClass' => null,
     *         'options' => [],
     *         'dsn' => null,
     *         'charset' => 'utf8mb4'
     *     ]
     * ]
     */
    public function load(array $config) :   AdapterInterface 
    {
        $config = $this->checkConfig($config);
        $name   = $config["adapter"];

        unset($config["adapter"]);
        $options = $config['options'] ?? [];
        return $this->newInstance(name, options);
    }

    /**
     * Create a new instance of the adapter
     */
    public function newInstance(string $name, array $options = []) : AdapterInterface
    {
        $definition = $this->getService($name);

        return Create::instance_params(
            $definition, [ $options ]
        );
    }

    /**
     * Returns the available adapters
     */
    protected function getAdapters() : array
    {
        return [
            "mysql"      => "Phalcon\\Db\\Adapter\\Pdo\\Mysql",
            "postgresql" =>  "Phalcon\\Db\\Adapter\\Pdo\\Postgresql",
            "sqlite"     =>  "Phalcon\\Db\\Adapter\\Pdo\\Sqlite"
        ];
    }
}
