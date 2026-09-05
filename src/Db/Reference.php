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

namespace Phalcon\Db;

use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\Exceptions\ForeignKeyColumnsRequired;
use Phalcon\Db\Exceptions\ReferencedColumnCountMismatch;
use Phalcon\Db\Exceptions\ReferencedColumnsRequired;
use Phalcon\Db\Exceptions\ReferencedTableRequired;

/**
 * Allows to define reference constraints on tables
 *
 *```php
 * $reference = new \Phalcon\Db\Reference(
 *     "field_fk",
 *     [
 *         "referencedSchema"  => "invoicing",
 *         "referencedTable"   => "products",
 *         "columns"           => [
 *             "producttype",
 *             "product_code",
 *         ],
 *         "referencedColumns" => [
 *             "type",
 *             "code",
 *         ],
 *     ]
 * );
 *```
 *
 * @phpstan-import-type db_column_names from DbTypes
 * @phpstan-import-type db_reference_definition from DbTypes
 */
class Reference implements ReferenceInterface
{
    /**
     * Local reference columns
     *
     * @var db_column_names
     */
    protected array $columns;
    /**
     * ON DELETE
     */
    protected ?string $onDelete = null;
    /**
     * ON UPDATE
     */
    protected ?string $onUpdate = null;
    /**
     * Referenced Columns
     *
     * @var db_column_names
     */
    protected array $referencedColumns;
    /**
     * Referenced Schema
     */
    protected ?string $referencedSchema = null;
    /**
     * Referenced Table
     */
    protected string $referencedTable;
    /**
     * Schema name
     */
    protected ?string $schemaName = null;

    /**
     * Phalcon\Db\Reference constructor
     *
     * @phpstan-param db_reference_definition $definition
     *
     * @throws Exception
     */
    public function __construct(
        protected string $name,
        array $definition
    ) {
        if (!isset($definition["referencedTable"])) {
            throw new ReferencedTableRequired();
        }

        $this->referencedTable = $definition["referencedTable"];

        if (!isset($definition["columns"])) {
            throw new ForeignKeyColumnsRequired();
        }

        $this->columns = $definition["columns"];

        if (!isset($definition["referencedColumns"])) {
            throw new ReferencedColumnsRequired();
        }

        $this->referencedColumns = $definition["referencedColumns"];

        $this->schemaName       = $definition["schema"] ?? null;
        $this->referencedSchema = $definition["referencedSchema"] ?? null;
        $this->onDelete         = $definition["onDelete"] ?? null;
        $this->onUpdate         = $definition["onUpdate"] ?? null;

        if (count($this->columns) !== count($this->referencedColumns)) {
            throw new ReferencedColumnCountMismatch();
        }
    }

    /**
     * Local reference columns
     *
     * @phpstan-return db_column_names
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Constraint name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * ON DELETE
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    /**
     * ON UPDATE
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * Referenced Columns
     *
     * @phpstan-return db_column_names
     */
    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }

    /**
     * Referenced Schema
     */
    public function getReferencedSchema(): ?string
    {
        return $this->referencedSchema;
    }

    /**
     * Referenced Table
     */
    public function getReferencedTable(): string
    {
        return $this->referencedTable;
    }

    /**
     * Schema name
     */
    public function getSchemaName(): ?string
    {
        return $this->schemaName;
    }
}
