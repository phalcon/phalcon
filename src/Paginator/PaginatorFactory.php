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
use Phalcon\Factory\AbstractFactory;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Adapter\AdapterInterface;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\Adapter\QueryBuilderCursor;
use Throwable;

/**
 * @phpstan-type TOptions array{
 *      adapter: string,
 *      limit?: int,
 *      page?: int,
 *      builder?: Builder
 * }
 */
class PaginatorFactory extends AbstractFactory
{
    /**
     * AdapterFactory constructor.
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
     *      ->columns("inv_id, inv_title")
     *      ->from(Invoices::class)
     *      ->orderBy("inv_title");
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
     * @param Config|TOptions $config
     */
    public function load(mixed $config): AdapterInterface
    {
        $config  = $this->checkConfig($config);
        $config  = $this->checkConfigElement($config, "adapter");
        $name    = $config["adapter"];
        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);
        /** @var AdapterInterface $paginator */
        $paginator = new $definition($options);

        return $paginator;
    }

    /**
     * @return class-string<Throwable>
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
            "model"               => Model::class,
            "nativeArray"         => NativeArray::class,
            "queryBuilder"        => QueryBuilder::class,
            "queryBuilderCursor"  => QueryBuilderCursor::class,
        ];
    }
}
