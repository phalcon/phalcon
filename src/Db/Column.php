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

use Phalcon\Db\Exceptions\ColumnTypeRejectsAutoIncrement;
use Phalcon\Db\Exceptions\ColumnTypeRejectsScale;
use Phalcon\Db\Exceptions\ColumnTypeRequired;
use Phalcon\Db\Exceptions\GeneratedAutoIncrementConflict;
use Phalcon\Db\Exceptions\GeneratedDefaultConflict;
use Phalcon\Db\Exceptions\InvalidGenerationExpression;

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
 * $connection->addColumn("co_invoices", null, $column);
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
    public const TYPE_BINARY = 27;
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
     * PostgreSQL `BYTEA` binary type
     */
    public const TYPE_BYTEA = 30;

    /**
     * Char abstract data type
     */
    public const TYPE_CHAR = 5;

    /**
     * PostgreSQL `CIDR` network-address type
     */
    public const TYPE_CIDR = 32;

    /**
     * Date abstract data type
     */
    public const TYPE_DATE = 1;

    /**
     * PostgreSQL `DATERANGE` range-of-date type
     */
    public const TYPE_DATERANGE = 39;

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
     * Spatial `GEOMETRY` base type (MySQL 5.7+; PostgreSQL + PostGIS)
     */
    public const TYPE_GEOMETRY = 40;

    /**
     * Spatial `GEOMETRYCOLLECTION` type
     */
    public const TYPE_GEOMETRYCOLLECTION = 47;

    /**
     * PostgreSQL `INET` IPv4/IPv6 address type
     */
    public const TYPE_INET = 31;

    /**
     * PostgreSQL `INT4RANGE` range-of-integer type
     */
    public const TYPE_INT4RANGE = 34;

    /**
     * PostgreSQL `INT8RANGE` range-of-bigint type
     */
    public const TYPE_INT8RANGE = 35;

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
     * Spatial `LINESTRING` type
     */
    public const TYPE_LINESTRING = 42;

    /**
     * Longblob abstract data type
     */
    public const TYPE_LONGBLOB = 13;

    /**
     * Longtext abstract data type
     */
    public const TYPE_LONGTEXT = 24;

    /**
     * PostgreSQL `MACADDR` MAC-address type
     */
    public const TYPE_MACADDR = 33;

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
     * Spatial `MULTILINESTRING` type
     */
    public const TYPE_MULTILINESTRING = 45;

    /**
     * Spatial `MULTIPOINT` type
     */
    public const TYPE_MULTIPOINT = 44;

    /**
     * Spatial `MULTIPOLYGON` type
     */
    public const TYPE_MULTIPOLYGON = 46;

    /**
     * PostgreSQL `NUMRANGE` range-of-numeric type
     */
    public const TYPE_NUMRANGE = 36;

    /**
     * Spatial `POINT` type
     */
    public const TYPE_POINT = 41;

    /**
     * Spatial `POLYGON` type
     */
    public const TYPE_POLYGON = 43;

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
     * PostgreSQL `TSRANGE` range-of-timestamp (without time zone) type
     */
    public const TYPE_TSRANGE = 37;

    /**
     * PostgreSQL `TSTZRANGE` range-of-timestamp (with time zone) type
     */
    public const TYPE_TSTZRANGE = 38;

    /**
     * UUID abstract data type
     */
    public const TYPE_UUID = 29;

    /**
     * Varbinary abstract data type
     */
    public const TYPE_VARBINARY = 28;

    /**
     * Varchar abstract data type
     */
    public const TYPE_VARCHAR = 2;

    /**
     * Column Position
     *
     * @var string|null
     */
    protected ?string $after = null;

    /**
     * Bind Type
     *
     * @var int
     */
    protected int $bindType = 2;

    /**
     * Column's comment
     *
     * @var string|null
     */
    protected ?string $comment = null;

    /**
     * Default column value
     *
     * @var mixed|null
     */
    protected mixed $defaultValue = null;

    /**
     * Generation expression for `GENERATED ALWAYS AS (...)`. Null when the
     * column is not generated.
     *
     * @var string|null
     */
    protected ?string $generated = null;

    /**
     * Whether a generated column is `STORED` (true) or `VIRTUAL` (false).
     * PostgreSQL only supports `STORED` and emits it regardless of this
     * flag.
     *
     * @var bool
     */
    protected bool $generationStored = false;

    /**
     * Whether the column is an array of its base type (PostgreSQL).
     *
     * @var bool
     */
    protected bool $isArray = false;

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
     * Whether the column is declared `INVISIBLE` (MySQL 8.0.23+).
     *
     * @var bool
     */
    protected bool $isInvisible = false;

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
     * @var int|string
     */
    protected int | string $type;

    /**
     * Column data type reference
     *
     * @var int
     */
    protected int $typeReference = -1;

    /**
     * Column data type values
     *
     * @var array|string|int
     */
    protected array | string | int $typeValues = [];

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
            throw new ColumnTypeRequired();
        }

        $this->after         = $definition["after"] ?? null;
        $this->bindType      = $definition["bindType"] ?? 2;
        $this->comment       = $definition["comment"] ?? null;
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
                default                => throw new ColumnTypeRejectsScale(),
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
                    default                => throw new ColumnTypeRejectsAutoIncrement(),
                };
            }
        }

        /**
         * Generated/computed column expression. When a non-null string is
         * provided the column is marked as generated and DEFAULT /
         * AUTO_INCREMENT are no longer compatible at the dialect level.
         */
        if (array_key_exists("generated", $definition) && $definition["generated"] !== null) {
            $generated = $definition["generated"];

            if (!is_string($generated)) {
                throw new InvalidGenerationExpression();
            }

            if ($this->isAutoIncrement) {
                throw new GeneratedAutoIncrementConflict();
            }

            if ($this->defaultValue !== null) {
                throw new GeneratedDefaultConflict();
            }

            $this->generated = $generated;
        }

        /**
         * Storage flag for generated columns. true = STORED, false = VIRTUAL.
         */
        if (isset($definition["generationStored"])) {
            $this->generationStored = (bool) $definition["generationStored"];
        }

        /**
         * Whether the column is INVISIBLE (MySQL 8.0.23+).
         */
        if (isset($definition["invisible"])) {
            $this->isInvisible = (bool) $definition["invisible"];
        }

        /**
         * Whether the column is an array of its base type (PostgreSQL).
         */
        if (isset($definition["array"])) {
            $this->isArray = (bool) $definition["array"];
        }
    }

    /**
     * Check whether field absolute to position in table
     *
     * @return string|null
     */
    public function getAfterPosition(): ?string
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
     * @return string|null
     */
    public function getComment(): ?string
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
     * Returns the generation expression for a generated/computed column.
     * Returns null when the column is not generated.
     *
     * @return string|null
     */
    public function getGenerationExpression(): ?string
    {
        return $this->generated;
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
     * @return int|string
     */
    public function getType(): int | string
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
    public function getTypeValues(): array | string | int
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
     * Whether the column is an array of its base type. Recognized by the
     * PostgreSQL dialect (e.g. `INTEGER[]`, `TEXT[]`); MySQL and SQLite
     * ignore the flag.
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->isArray;
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
     * Whether the column is a generated/computed column.
     *
     * @return bool
     */
    public function isGenerated(): bool
    {
        return $this->generated !== null;
    }

    /**
     * Whether a generated column is `STORED`. `false` means `VIRTUAL`.
     *
     * @return bool
     */
    public function isGenerationStored(): bool
    {
        return $this->generationStored;
    }

    /**
     * Whether the column is declared `INVISIBLE` (MySQL 8.0.23+).
     *
     * @return bool
     */
    public function isInvisible(): bool
    {
        return $this->isInvisible;
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

    private function processColumnType(mixed $type): int | string
    {
        if (is_string($type)) {
            return $type;
        }

        $type = (int)$type;

        return match ($type) {
            self::TYPE_BIGINTEGER,
            self::TYPE_BINARY,
            self::TYPE_BIT,
            self::TYPE_BLOB,
            self::TYPE_BOOLEAN,
            self::TYPE_BYTEA,
            self::TYPE_CHAR,
            self::TYPE_CIDR,
            self::TYPE_DATE,
            self::TYPE_DATERANGE,
            self::TYPE_DATETIME,
            self::TYPE_DECIMAL,
            self::TYPE_DOUBLE,
            self::TYPE_ENUM,
            self::TYPE_FLOAT,
            self::TYPE_GEOMETRY,
            self::TYPE_GEOMETRYCOLLECTION,
            self::TYPE_INET,
            self::TYPE_INT4RANGE,
            self::TYPE_INT8RANGE,
            self::TYPE_INTEGER,
            self::TYPE_JSON,
            self::TYPE_JSONB,
            self::TYPE_LINESTRING,
            self::TYPE_LONGBLOB,
            self::TYPE_LONGTEXT,
            self::TYPE_MACADDR,
            self::TYPE_MEDIUMBLOB,
            self::TYPE_MEDIUMINTEGER,
            self::TYPE_MEDIUMTEXT,
            self::TYPE_MULTILINESTRING,
            self::TYPE_MULTIPOINT,
            self::TYPE_MULTIPOLYGON,
            self::TYPE_NUMRANGE,
            self::TYPE_POINT,
            self::TYPE_POLYGON,
            self::TYPE_SMALLINTEGER,
            self::TYPE_TEXT,
            self::TYPE_TIME,
            self::TYPE_TIMESTAMP,
            self::TYPE_TINYBLOB,
            self::TYPE_TINYINTEGER,
            self::TYPE_TINYTEXT,
            self::TYPE_TSRANGE,
            self::TYPE_TSTZRANGE,
            self::TYPE_UUID,
            self::TYPE_VARBINARY,
            self::TYPE_VARCHAR => $type,
            default            => throw new Exception(
                "Column type is not valid"
            ),
        };
    }
}
