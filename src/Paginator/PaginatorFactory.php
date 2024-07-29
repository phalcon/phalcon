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

namespace Phalcon\Paginator;

use Phalcon\Config\Config;
use Phalcon\Paginator\Adapter\AdapterInterface;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;

class PaginatorFactory
{
    use ConfigTrait;
    use FactoryTrait;

    /**
     * AdapterFactory constructor.
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
     *```php
     * use Phalcon\Paginator\PaginatorFactory;
     *
     * $builder = $this
     *      ->modelsManager
     *      ->createBuilder()
     *      ->columns("id, name")
     *      ->from(Robots::class)
     *      ->orderBy("name");
     *
     * $options = [
     *     "builder" => $builder,
     *     "limit"   => 20,
     *     "page"    => 1,
     *     "adapter" => "queryBuilder",
     * ];
     *
     * $paginator = (new PaginatorFactory())->load($options);
     *```
     *
     * @param array|Config $config = [
     *     'adapter' => 'queryBuilder',
     *     'limit' => 20,
     *     'page' => 1,
     *     'builder' => null
     * ]
     *
     * @throws Exception
     * @return AdapterInterface
     */
    public function load(array | Config $config): AdapterInterface
    {
        $config  = $this->checkConfig($config);
        $config  = $this->checkConfigElement($config, "adapter");
        $name    = $config["adapter"];
        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array $options
     *
     * @throws Exception
     * @return AdapterInterface
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        $instance = new $definition($options);
        if ($instance instanceof AdapterInterface) {
            return $instance;
        }

        throw $this->getException("$name is not an instance of \\Phalcon\\Paginator\\Adapter\\AdapterInterface");
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
            "model"        => Adapter\Model::class,
            "nativeArray"  => Adapter\NativeArray::class,
            "queryBuilder" => Adapter\QueryBuilder::class,
        ];
    }
}
