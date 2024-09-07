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

namespace Phalcon\DataMapper\Statement\Clause\Traits;

use Phalcon\DataMapper\Pdo\Connection;

use function is_array;
use function is_numeric;

/**
 * @property Connection $connection
 * @property array      $store
 *
 * @method void addCondition(string $store, string $andor, string $condition, mixed $value = null, int $type = -1)
 * @method void appendCondition(string $store, string $condition, mixed $value = null, int $type = -1)
 */
trait WhereTrait
{
    /**
     * Sets a `AND` for a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function andWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->where($condition, $value, $type);

        return $this;
    }

    /**
     * Concatenates to the most recent `WHERE` clause
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function appendWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->appendCondition("WHERE", $condition, $value, $type);

        return $this;
    }


    /**
     * Sets a `OR` for a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function orWhere(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition("WHERE", "OR ", $condition, $value, $type);

        return $this;
    }

    /**
     * Resets the where
     */
    public function resetWhere(): static
    {
        $this->store["WHERE"] = [];

        return $this;
    }

    /**
     * Sets a `WHERE` condition
     *
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     *
     * @return static
     */
    public function where(
        string $condition,
        mixed $value = null,
        int $type = -1
    ): static {
        $this->addCondition("WHERE", "AND ", $condition, $value, $type);

        return $this;
    }

    /**
     * @param array $columnsValues
     *
     * @return static
     */
    public function whereEquals(array $columnsValues): static
    {
        foreach ($columnsValues as $key => $value) {
            if (is_numeric($key)) {
                $this->where($value);
            } elseif (null === $value) {
                $this->where($key . " IS NULL");
            } elseif (is_array($value)) {
                $this->where($key . " IN ", $value);
            } else {
                $this->where($key . " = ", $value);
            }
        }

        return $this;
    }
}
