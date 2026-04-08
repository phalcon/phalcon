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

use function is_array;

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
 */
class Reference implements ReferenceInterface
{
    /**
     * Local reference columns
     *
     * @var array
     */
    protected array $columns;
    /**
     * ON DELETE
     *
     * @var string|null
     */
    protected ?string $onDelete = null;
    /**
     * ON UPDATE
     *
     * @var string|null
     */
    protected ?string $onUpdate = null;
    /**
     * Referenced Columns
     *
     * @var array
     */
    protected array $referencedColumns;
    /**
     * Referenced Schema
     *
     * @var string
     */
    protected string $referencedSchema;
    /**
     * Referenced Table
     *
     * @var string
     */
    protected string $referencedTable;
    /**
     * Schema name
     *
     * @var string
     */
    protected string $schemaName;

    /**
     * Phalcon\Db\Reference constructor
     *
     * @param string $name
     * @param array  $definition
     *
     * @throws Exception
     */
    public function __construct(
        protected string $name,
        array $definition
    ) {
        if (!isset($definition["referencedTable"])) {
            throw new Exception("Referenced table is required");
        }

        if (!isset($definition["columns"])) {
            throw new Exception("Foreign key columns are required");
        }

        if (!isset($definition["referencedColumns"])) {
            throw new Exception(
                "Referenced columns of the foreign key are required"
            );
        }

        if (!is_array($definition["columns"])) {
            throw new Exception("Foreign key columns must be an array");
        }

        if (!is_array(($definition["referencedColumns"]))) {
            throw new Exception(
                "Referenced columns of the foreign key must be an array"
            );
        }

        $this->columns           = $definition["columns"];
        $this->referencedColumns = $definition["referencedColumns"];

        if (count($this->columns) !== count($this->referencedColumns)) {
            throw new Exception(
                "Number of columns is not equals than the number of columns referenced"
            );
        }

        $this->referencedTable  = $definition["referencedTable"];
        $this->schemaName       = $definition["schema"] ?? "";
        $this->referencedSchema = $definition["referencedSchema"] ?? "";
        $this->onDelete         = $definition["onDelete"] ?? null;
        $this->onUpdate         = $definition["onUpdate"] ?? null;
    }

    /**
     * Local reference columns
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Constraint name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * ON DELETE
     *
     * @return string|null
     */
    public function getOnDelete(): ?string
    {
        return $this->onDelete;
    }

    /**
     * ON UPDATE
     *
     * @return string|null
     */
    public function getOnUpdate(): ?string
    {
        return $this->onUpdate;
    }

    /**
     * Referenced Columns
     *
     * @return array
     */
    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }

    /**
     * Referenced Schema
     *
     * @return string
     */
    public function getReferencedSchema(): string
    {
        return $this->referencedSchema;
    }

    /**
     * Referenced Table
     *
     * @return string
     */
    public function getReferencedTable(): string
    {
        return $this->referencedTable;
    }

    /**
     * Schema name
     *
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaName;
    }
}
