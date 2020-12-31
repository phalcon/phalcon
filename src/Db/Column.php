<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

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
    const BIND_PARAM_BLOB = 3;

    /**
     * Bind Type Bool
     */
    const BIND_PARAM_BOOL = 5;

    /**
     * Bind Type Decimal
     */
    const BIND_PARAM_DECIMAL = 32;

    /**
     * Bind Type Integer
     */
    const BIND_PARAM_INT = 1;

    /**
     * Bind Type Null
     */
    const BIND_PARAM_NULL = 0;

    /**
     * Bind Type String
     */
    const BIND_PARAM_STR = 2;

    /**
     * Skip binding by type
     */
    const BIND_SKIP = 1024;

    /**
     * Big integer abstract data type
     */
    const TYPE_BIGINTEGER = 14;

    /**
     * Bit abstract data type
     */
    const TYPE_BIT = 19;

    /**
     * Blob abstract data type
     */
    const TYPE_BLOB = 11;

    /**
     * Bool abstract data type
     */
    const TYPE_BOOLEAN = 8;

    /**
     * Char abstract data type
     */
    const TYPE_CHAR = 5;

    /**
     * Date abstract data type
     */
    const TYPE_DATE = 1;

    /**
     * Datetime abstract data type
     */
    const TYPE_DATETIME = 4;

    /**
     * Decimal abstract data type
     */
    const TYPE_DECIMAL = 3;

    /**
     * Double abstract data type
     */
    const TYPE_DOUBLE = 9;

    /**
     * Enum abstract data type
     */
    const TYPE_ENUM = 18;

    /**
     * Float abstract data type
     */
    const TYPE_FLOAT = 7;

    /**
     * Int abstract data type
     */
    const TYPE_INTEGER = 0;

    /**
     * Json abstract data type
     */
    const TYPE_JSON = 15;

    /**
     * Jsonb abstract data type
     */
    const TYPE_JSONB = 16;

    /**
     * Longblob abstract data type
     */
    const TYPE_LONGBLOB = 13;

    /**
     * Longtext abstract data type
     */
    const TYPE_LONGTEXT = 24;

    /**
     * Mediumblob abstract data type
     */
    const TYPE_MEDIUMBLOB = 12;

    /**
     * Mediumintegerr abstract data type
     */
    const TYPE_MEDIUMINTEGER = 21;

    /**
     * Mediumtext abstract data type
     */
    const TYPE_MEDIUMTEXT = 23;

    /**
     * Smallint abstract data type
     */
    const TYPE_SMALLINTEGER = 22;

    /**
     * Text abstract data type
     */
    const TYPE_TEXT = 6;

    /**
     * Time abstract data type
     */
    const TYPE_TIME = 20;

    /**
     * Timestamp abstract data type
     */
    const TYPE_TIMESTAMP = 17;

    /**
     * Tinyblob abstract data type
     */
    const TYPE_TINYBLOB = 10;

    /**
     * Tinyint abstract data type
     */
    const TYPE_TINYINTEGER = 26;

    /**
     * Tinytext abstract data type
     */
    const TYPE_TINYTEXT = 25;

    /**
     * Varchar abstract data type
     */
    const TYPE_VARCHAR = 2;

    /**
     * Column Position
     *
     * @var string|null
     */
    protected $after;

    /**
     * Column is autoIncrement?
     *
     * @var bool
     */
    protected bool $autoIncrement = false;

    /**
     * Bind Type
     */
    protected int $bindType = 2;

    /**
     * Default column value
     */
    protected $_default = null;

    /**
     * Position is first
     *
     * @var bool
     */
    protected bool $first = false;

    /**
     * The column have some numeric type?
     */
    protected bool $isNumeric = false;

    /**
     * Column's name
     *
     * @var string
     */
    protected string $name;

    /**
     * Column's comment
     *
     * @var string
     */
     protected ?string $comment = null;

    /**
     * Column not nullable?
     *
     * Default SQL definition is NOT NULL.
     *
     * @var bool
     */
    protected bool $notNull = true;

    /**
     * Column is part of the primary key?
     */
    protected bool $primary = false;

    /**
     * Integer column number scale
     *
     * @var int
     */
    protected int $scale = 0;

    /**
     * Integer column size
     *
     * @var int | string
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
    protected array | string $typeValues;

    /**
     * Integer column unsigned?
     *
     * @var bool
     */
    protected bool $unsigned = false;

    /**
     * Phalcon\Db\Column constructor
     */
    public function __construct(string $name, array $definition)
    {
        $this->name = $name;

        /**
         * Get the column type, one of the TYPE_* constants
         */
        $type = $definition["type"] ?? null;
        if ($type === null) {
            throw new Exception("Column type is required for " . $name);
        }
        $this->type = $type;

        $typeReference = $definition["typeReference"] ?? null;
        if ($typeReference !== null) {
            $this->typeReference = $typeReference;
        }

        $typeValues = $definition["typeValues"] ?? null;
        if ($typeValues !== null){
             $this->typeValues = $typeValues;
        }

        /**
         * Check if the field is nullable
         */
        $notNull = $definition["notNull"] ?? null;
        if ($notNull !== null) {
             $this->notNull = $notNull;
        }

        /**
         * Check if the field is primary key
         */
        $primary = $definition["primary"] ?? null;
        if ($primary !== null)  {
             $this->primary = $primary;
        }
        $size = $definition["size"] ?? null;
        if ($size !== null)  {
             $this->size = $size;
        }

        /**
         * Check if the column has a decimal scale
         */
        $scale = $definition["scale"] ?? null;
        if ($scale !== null)  {
            switch ($type) {
                case self::TYPE_BIGINTEGER:
                case self::TYPE_DECIMAL:
                case self::TYPE_DOUBLE:
                case self::TYPE_FLOAT:
                case self::TYPE_INTEGER:
                case self::TYPE_MEDIUMINTEGER:
                case self::TYPE_SMALLINTEGER:
                case self::TYPE_TINYINTEGER:
                     $this->scale = $scale;
                    break;

                default:
                    throw new Exception(
                        "Column type does not support scale parameter"
                    );
            }
        }

        /**
         * Check if the column is default value
         */
        $defaultValue = $definition["default"] ?? null;
		if ($defaultValue !== null) {
             $this->_default = $defaultValue;
        }

        /**
         * Check if the field is unsigned (only MySQL)
         */
        $dunsigned = $definition["unsigned"] ?? null;
		if ($dunsigned !== null) {
             $this->unsigned = $dunsigned;
        }

        /**
         * Check if the field is numeric
         */
        $isNumeric = $definition["isNumeric"] ?? null;
		if ($isNumeric !== null) {
             $this->isNumeric = $isNumeric;
        }

        /**
         * Check if the field is auto-increment/serial
         */
        $autoIncrement = $definition["autoIncrement"] ?? null;
		if ($autoIncrement !== null) {
            if (!$autoIncrement) {
                 $this->autoIncrement = false;
            } else {
                switch ($type) {
                    case self::TYPE_BIGINTEGER:
                    case self::TYPE_INTEGER:
                    case self::TYPE_MEDIUMINTEGER:
                    case self::TYPE_SMALLINTEGER:
                    case self::TYPE_TINYINTEGER:
                         $this->autoIncrement = true;
                        break;

                    default:
                        throw new Exception(
                            "Column type cannot be auto-increment"
                        );
                }
            }
        }

        /**
         * Check if the field is placed at the first position of the table
         */
        $first = $definition["first"] ?? null;
		if ($first !== null) {
             $this->first = $first;
        }

        /**
         * Name of the column which is placed before the current field
         */
        $after = $definition["after"] ?? null;
		if ($after !== null) {
             $this->after = $after;
        }

        /**
         * The bind type to cast the field when passing it to PDO
         */
        $bindType = $definition["bindType"] ?? null;
		if ($bindType !== null) {
             $this->bindType = $bindType;
        }

        /**
         * Get the column comment
         */
         $comment = $definition["comment"] ?? null;
		if ($comment !== null) {
             $this->comment = $comment;
        }
    }

    /**
     * Check whether field absolute to position in table
     */
    public function getAfterPosition() : string | null
    {
        return $this->after;
    }

    /**
     * Returns the type of bind handling
     */
    public function getBindType() : int
    {
        return $this->bindType;
    }

    /**
     * Check whether column has default value
     */
    public function hasDefault() : bool
    {
        if ($this->isAutoIncrement()) {
            return false;
        }

        return $this->_default !== null;
    }

    public function getDefault() : mixed
    {
        return $this->_default;
    }
    
    public function getName() : string
    {
        return $this->name;
    }
    
    public function getComment() : ?string
    {
        return $this->comment;
    }
    
    public function getScale() : int 
    {
        return $this->size;
    }
    
    public function getSize() : int | string 
    {
        return $this->size;
    }
    
    public function getType() : int
    {
        return $this->type;
    }
    public function getTypeValues() : array | string
    {
        return $this->typeValues;
    }
    public function getTypeReference() : int
    {
        return $this->typeReferenc;
    }
    
    /**
     * Auto-Increment
     */
    public function isAutoIncrement() : bool
    {
        return $this->autoIncrement;
    }

    /**
     * Check whether column have first position in table
     */
    public function isFirst() : bool
    {
        return $this->first;
    }

    /**
     * Not null
     */
    public function isNotNull() : bool
    {
        return $this->notNull;
    }

    /**
     * Check whether column have an numeric type
     */
    public function isNumeric() : bool
    {
        return $this->isNumeric;
    }

    /**
     * Column is part of the primary key?
     */
    public function isPrimary() : bool
    {
        return $this->primary;
    }

    /**
     * Returns true if number column is unsigned
     */
    public function isUnsigned() : bool
    {
        return $this->unsigned;
    }
}
