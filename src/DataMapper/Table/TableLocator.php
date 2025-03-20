<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Table
 * @license https://github.com/atlasphp/Atlas.Table/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Table;

use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Pdo\Exception\ConnectionNotFound;
use Phalcon\DataMapper\Table\Exception\TableClassMissingException;

use function class_exists;
use function is_subclass_of;

class TableLocator
{
    /**
     * @var array<string, AbstractTable>
     */
    protected array $instances = [];

    /**
     * @param ConnectionLocator $connectionLocator
     * @param callable|null     $factory
     */
    public function __construct(
        protected ConnectionLocator $connectionLocator,
        protected mixed $factory = null
    ) {
        $this->factory ??= fn(string $class): object => new $class();
    }

    /**
     * Return a stored table class. Throw an exception if the class does not
     * exist or is not an instance of AbstractTable. Create it if it is not
     * already instantiated.
     *
     * @param string $tableClass
     *
     * @return AbstractTable
     * @throws TableClassMissingException
     */
    public function get(string $tableClass): AbstractTable
    {
        if (true !== $this->has($tableClass)) {
            throw new TableClassMissingException($tableClass);
        }

        if (true !== isset($this->instances[$tableClass])) {
            $this->instances[$tableClass] = $this->newTable($tableClass);
        }

        return $this->instances[$tableClass];
    }

    /**
     * Return the ConnectionLocator instance
     *
     * @return ConnectionLocator
     */
    public function getConnectionLocator(): ConnectionLocator
    {
        return $this->connectionLocator;
    }

    /**
     * Return true if the class exists and is an instance of AbstractTable.
     *
     * @param string $tableClass
     *
     * @return bool
     */
    public function has(string $tableClass): bool
    {
        return class_exists($tableClass) &&
            is_subclass_of($tableClass, AbstractTable::class);
    }

    /**
     * @param mixed ...$arguments
     *
     * @return static
     * @throws ConnectionNotFound
     */
    public static function new(mixed ...$arguments): static
    {
        return new static(ConnectionLocator::new(...$arguments));
    }

    /**
     * Return a new table class
     *
     * @param string $tableClass
     *
     * @return AbstractTable
     */
    protected function newTable(string $tableClass): AbstractTable
    {
        /** @var AbstractTable $table */
        $table = new ('\\' . $tableClass)(
            $this->connectionLocator,
            ($this->factory)($tableClass . 'Events')
        );

        return $table;
    }
}
