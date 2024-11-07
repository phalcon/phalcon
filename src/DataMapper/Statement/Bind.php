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
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use PDO;

use function array_map;
use function implode;
use function is_array;
use function is_bool;
use function is_int;

/**
 * Class Bind
 */
class Bind
{
    /**
     * @var int
     */
    protected int $inlineCount = 0;
    /**
     * @var int
     */
    protected int $inlinePrefix = 0;
    /**
     * @var int
     */
    protected static int $instanceCount = 0;
    /**
     * @var array<string, array<array-key, mixed>>
     */
    protected array $store = [];

    public function __construct()
    {
        $this->incrementInstanceCount();
    }

    /**
     * Increment the internal count when cloning
     *
     * @return void
     */
    public function __clone()
    {
        $this->incrementInstanceCount();
    }

    /**
     * @param mixed $value
     * @param int   $type
     *
     * @return string
     */
    public function bindInline(mixed $value, int $type = -1): string
    {
        if ($value instanceof Select) {
            $this->store += $value->getBindValues();

            return '(' . $value->getStatement() . ')';
        }

        if (is_array($value)) {
            return $this->inlineArray($value, $type);
        }

        return ':' . $this->inlineValue($value, $type);
    }

    /**
     * Merge values with the internal collection
     *
     * @param array $values
     *
     * @return void
     */
    public function merge(array $values): void
    {
        $this->store += $values;
    }

    /**
     * Removes a value from the store
     *
     * @param string $key
     */
    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }

    /**
     * Reset the internal stores
     *
     * @return void
     */
    public function reset(): void
    {
        $this->inlineCount = 0;
        $this->store       = [];
    }

    /**
     * Sets a value
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $type
     */
    public function setValue(string $key, mixed $value, int $type = -1): void
    {
        $localType = $type === -1 ? $this->getType($value) : $type;

        $this->store[$key] = [$value, $localType];
    }

    /**
     * Sets values from an array
     *
     * @param array $values
     * @param int   $type
     */
    public function setValues(array $values, int $type = -1): void
    {
        foreach ($values as $key => $value) {
            $this->setValue($key, $value, $type);
        }
    }

    /**
     * Returns the internal collection
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->store;
    }

    /**
     * Auto detects the PDO type
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function getType(mixed $value): int
    {
        return match (true) {
            $value === null => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            is_int($value)  => PDO::PARAM_INT,
            default         => PDO::PARAM_STR,
        };
    }

    /**
     * Increment the internal instance count
     *
     * @return void
     */
    protected function incrementInstanceCount(): void
    {
        static::$instanceCount++;
        $this->inlinePrefix = static::$instanceCount;
    }

    /**
     * Processes an array - if passed as an `inline` parameter
     *
     * @param array<string, mixed> $data
     * @param int                  $type
     *
     * @return string
     */
    protected function inlineArray(array $data, int $type): string
    {
        $keys = array_map(
            fn($value) => ':' . $this->inlineValue($value, $type),
            $data
        );

        return '(' . implode(', ', $keys) . ')';
    }

    /**
     * Calculate the key and add the value in the internal collection
     *
     * @param mixed $value
     * @param int   $type
     *
     * @return string
     */
    protected function inlineValue(mixed $value, int $type): string
    {
        $this->inlineCount++;
        $key = '_' . $this->inlinePrefix . '_' . $this->inlineCount . '_';
        $this->setValue($key, $value, $type);

        return $key;
    }
}
