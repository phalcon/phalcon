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

use Phalcon\DataMapper\Query\Select;
use Phalcon\DataMapper\Table\Exception\TableAlreadySetException;

use function array_pop;

abstract class AbstractTableSelect extends Select
{
    protected AbstractTable $table;

    /**
     * Return the number of rows in the result set.
     *
     * @param string $column
     *
     * @return int
     */
    public function fetchCount(string $column = '*'): int
    {
        $select = clone $this;
        $select
            ->resetColumns()
            ->resetLimit()
            ->columns(["COUNT($column)"])
        ;

        return (int)$this->table->getReadConnection()->fetchValue(
            $select->getStatement(),
            $select->getBindValues()
        );
    }

    /**
     * @return AbstractRow|null
     */
    public function fetchRow(): AbstractRow | null
    {
        $columns = $this->fetchOne();

        return true === empty($columns)
            ? null
            : $this->table->newSelectedRow($columns);
    }

    /**
     * Return the rows of a resultset as an array
     *
     * @return AbstractRow[]
     */
    public function fetchRows(): array
    {
        $rows = [];
        foreach ($this->yieldAll() as $columns) {
            $rows[] = $this->table->newSelectedRow($columns);
        }

        return $rows;
    }

    /**
     * Returns a new TableSelect object.
     *
     * @param mixed $argument
     * @param mixed ...$arguments
     *
     * @return static
     */
    public static function new(mixed $argument, mixed ...$arguments): static
    {
        /** @var array $whereEquals */
        $whereEquals = array_pop($arguments) ?? [];

        /** @var AbstractTable $table */
        $table  = array_pop($arguments);
        $select = parent::new($argument, ...$arguments);

        $select->table = $table;

        $select->from($select->quoteIdentifier($table::NAME));
        $select->whereEquals($whereEquals);

        return $select;
    }
}
