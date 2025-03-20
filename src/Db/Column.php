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

/**
 * Allows to define columns to be used on create or alter table operations
 *
 *```php
 * use Phalcon\Db\Column as Column;
 *
 * // Column definition
 * $column = new Column(
 *     "id",
 *     [
 *         "type"          => Column::TYPE_INTEGER,
 *         "size"          => 10,
 *         "unsigned"      => true,
 *         "notNull"       => true,
 *         "autoIncrement" => true,
 *         "first"         => true,
 *         "comment"       => "",
 *     ]
 * );
 *
 * // Add column to existing table
 * $connection->addColumn("robots", null, $column);
 *```
 */
class Column implements ColumnInterface
{
    /**
     * Bind Type Blob
     */
    public const BIND_PARAM_BLOB = 3;

    /**
     * Bind Type Bool
     */
    public const BIND_PARAM_BOOL = 5;

    /**
     * Bind Type Decimal
     */
    public const BIND_PARAM_DECIMAL = 32;

    /**
     * Bind Type Integer
     */
    public const BIND_PARAM_INT = 1;

    /**
     * Bind Type Null
     */
    public const BIND_PARAM_NULL = 0;

    /**
     * Bind Type String
     */
    public const BIND_PARAM_STR = 2;

    /**
     * Skip binding by type
     */
    public const BIND_SKIP = 1024;

    /**
     * Big integer abstract data type
     */
    public const TYPE_BIGINTEGER = 14;
    /**
     * Binary abstract data type
     */
    public const TYPE_BINARY = 26;
    /**
     * Bit abstract data type
     */
    public const TYPE_BIT = 19;
    /**
     * Blob abstract data type
     */
    public const TYPE_BLOB = 11;

    /**
     * Bool abstract data type
     */
    public const TYPE_BOOLEAN = 8;

    /**
     * Char abstract data type
     */
    public const TYPE_CHAR = 5;

    /**
     * Date abstract data type
     */
    public const TYPE_DATE = 1;

    /**
     * Datetime abstract data type
     */
    public const TYPE_DATETIME = 4;

    /**
     * Decimal abstract data type
     */
    public const TYPE_DECIMAL = 3;

    /**
     * Double abstract data type
     */
    public const TYPE_DOUBLE = 9;

    /**
     * Enum abstract data type
     */
    public const TYPE_ENUM = 18;

    /**
     * Float abstract data type
     */
    public const TYPE_FLOAT = 7;

    /**
     * Int abstract data type
     */
    public const TYPE_INTEGER = 0;

    /**
     * Json abstract data type
     */
    public const TYPE_JSON = 15;

    /**
     * Jsonb abstract data type
     */
    public const TYPE_JSONB = 16;

    /**
     * Longblob abstract data type
     */
    public const TYPE_LONGBLOB = 13;

    /**
     * Longtext abstract data type
     */
    public const TYPE_LONGTEXT = 24;

    /**
     * Mediumblob abstract data type
     */
    public const TYPE_MEDIUMBLOB = 12;

    /**
     * Mediumintegerr abstract data type
     */
    public const TYPE_MEDIUMINTEGER = 21;

    /**
     * Mediumtext abstract data type
     */
    public const TYPE_MEDIUMTEXT = 23;

    /**
     * Smallint abstract data type
     */
    public const TYPE_SMALLINTEGER = 22;

    /**
     * Text abstract data type
     */
    public const TYPE_TEXT = 6;

    /**
     * Time abstract data type
     */
    public const TYPE_TIME = 20;

    /**
     * Timestamp abstract data type
     */
    public const TYPE_TIMESTAMP = 17;

    /**
     * Tinyblob abstract data type
     */
    public const TYPE_TINYBLOB = 10;

    /**
     * Tinyint abstract data type
     */
    public const TYPE_TINYINTEGER = 26;

    /**
     * Tinytext abstract data type
     */
    public const TYPE_TINYTEXT = 25;

    /**
     * Varbinary abstract data type
     */
    public const TYPE_VARBINARY = 27;

    /**
     * Varchar abstract data type
     */
    public const TYPE_VARCHAR = 2;

    /**
     * Column Position
     *
     * @var string
     */
    protected string $after = "";

    /**
     * Bind Type
     *
     * @var int
     */
    protected int $bindType = 2;

    /**
     * Column's comment
     *
     * @var string
     */
    protected string $comment = "";

    /**
     * Default column value
     *
     * @var mixed|null
     */
    protected mixed $defaultValue = null;

    /**
     * Column is autoIncrement?
     *
     * @var bool
     */
    protected bool $isAutoIncrement = false;

    /**
     * Position is first
     *
     * @var bool
     */
    protected bool $isFirst = false;

    /**
     * Column not nullable?
     *
     * Default SQL definition is NOT NULL.
     *
     * @var bool
     */
    protected bool $isNotNull = true;

    /**
     * The column have some numeric type?
     *
     * @var bool
     */
    protected bool $isNumeric = false;

    /**
     * Column is part of the primary key?
     *
     * @var bool
     */
    protected bool $isPrimary = false;

    /**
     * Integer column unsigned?
     *
     * @var bool
     */
    protected bool $isUnsigned = false;

    /**
     * Integer column number scale
     *
     * @var int
     */
    protected int $scale = 0;

    /**
     * Integer column size
     *
     * @var int|string
     */
    protected int | string $size = 0;

    /**
     * Column data type
     *
     * @var int
     */
    protected int $type;

    /**
     * Column data type reference
     *
     * @var int
     */
    protected int $typeReference = -1;

    /**
     * Column data type values
     *
     * @var array|string
     */
    protected array | string $typeValues = [];

    /**
     * Phalcon\Db\Column constructor
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
        /**
         * Get the column type, one of the TYPE_* constants
         */
        if (!isset($definition["type"])) {
            throw new Exception("Column type is required");
        }

        $this->after         = $definition["after"] ?? "";
        $this->bindType      = $definition["bindType"] ?? 2;
        $this->comment       = $definition["comment"] ?? "";
        $this->defaultValue  = $definition["default"] ?? null;
        $this->isFirst       = (bool)($definition["first"] ?? false);
        $this->isNotNull     = (bool)($definition["notNull"] ?? true);
        $this->isNumeric     = (bool)($definition["isNumeric"] ?? false);
        $this->isPrimary     = (bool)($definition["primary"] ?? false);
        $this->isUnsigned    = (bool)($definition["unsigned"] ?? false);
        $this->size          = $definition["size"] ?? 0;
        $this->type          = $this->processColumnType($definition["type"]);
        $this->typeReference = $definition["typeReference"] ?? -1;
        $this->typeValues    = $definition["typeValues"] ?? [];

        /**
         * Check if the column has a decimal scale
         */
        if (isset($definition["scale"])) {
            $this->scale = match ($this->type) {
                self::TYPE_BIGINTEGER,
                self::TYPE_DECIMAL,
                self::TYPE_DOUBLE,
                self::TYPE_FLOAT,
                self::TYPE_INTEGER,
                self::TYPE_MEDIUMINTEGER,
                self::TYPE_SMALLINTEGER,
                self::TYPE_TINYINTEGER => $definition["scale"],
                default                => throw new Exception(
                    "Column type does not support scale parameter"
                ),
            };
        }

        /**
         * Check if the field is auto-increment/serial
         */
        if (isset($definition["autoIncrement"])) {
            $autoIncrement = $definition["autoIncrement"];
            if (true === $autoIncrement) {
                $this->isAutoIncrement = match ($this->type) {
                    self::TYPE_BIGINTEGER,
                    self::TYPE_INTEGER,
                    self::TYPE_MEDIUMINTEGER,
                    self::TYPE_SMALLINTEGER,
                    self::TYPE_TINYINTEGER => true,
                    default                => throw new Exception(
                        "Column type cannot be auto-increment"
                    ),
                };
            }
        }
    }

    /**
     * Check whether field absolute to position in table
     *
     * @return string
     */
    public function getAfterPosition(): string
    {
        return $this->after;
    }

    /**
     * Returns the type of bind handling
     *
     * @return int
     */
    public function getBindType(): int
    {
        return $this->bindType;
    }

    /**
     * Column's comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Default column value
     *
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Column's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Integer column number scale
     *
     * @return int
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * Integer column size
     *
     * @return int|string
     */
    public function getSize(): int | string
    {
        return $this->size;
    }

    /**
     * Column data type
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Column data type reference
     *
     * @return int
     */
    public function getTypeReference(): int
    {
        return $this->typeReference;
    }

    /**
     * Column data type values
     *
     * @return array|string
     */
    public function getTypeValues(): array | string
    {
        return $this->typeValues;
    }

    /**
     * Check whether column has default value
     *
     * @return bool
     */
    public function hasDefault(): bool
    {
        if ($this->isAutoIncrement) {
            return false;
        }

        return $this->defaultValue !== null;
    }

    /**
     * Auto-Increment
     *
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }

    /**
     * Check whether column has the first position in the table
     *
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->isFirst;
    }

    /**
     * Not null
     *
     * @return bool
     */
    public function isNotNull(): bool
    {
        return $this->isNotNull;
    }

    /**
     * Check whether column have a numeric type
     *
     * @return bool
     */
    public function isNumeric(): bool
    {
        return $this->isNumeric;
    }

    /**
     * Column is part of the primary key?
     *
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * Returns true if number column is unsigned
     *
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->isUnsigned;
    }

    private function processColumnType(mixed $type): int
    {
        $type = (int)$type;

        return match ($type) {
            self::TYPE_BIGINTEGER,
            self::TYPE_BINARY,
            self::TYPE_BIT,
            self::TYPE_BLOB,
            self::TYPE_BOOLEAN,
            self::TYPE_CHAR,
            self::TYPE_DATE,
            self::TYPE_DATETIME,
            self::TYPE_DECIMAL,
            self::TYPE_DOUBLE,
            self::TYPE_ENUM,
            self::TYPE_FLOAT,
            self::TYPE_INTEGER,
            self::TYPE_JSON,
            self::TYPE_JSONB,
            self::TYPE_LONGBLOB,
            self::TYPE_LONGTEXT,
            self::TYPE_MEDIUMBLOB,
            self::TYPE_MEDIUMINTEGER,
            self::TYPE_MEDIUMTEXT,
            self::TYPE_SMALLINTEGER,
            self::TYPE_TEXT,
            self::TYPE_TIME,
            self::TYPE_TIMESTAMP,
            self::TYPE_TINYBLOB,
            self::TYPE_TINYINTEGER,
            self::TYPE_TINYTEXT,
            self::TYPE_VARBINARY,
            self::TYPE_VARCHAR => $type,
            default            => throw new Exception(
                "Column type is not valid"
            ),
        };
    }
}
