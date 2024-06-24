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

namespace Phalcon\DataMapper\Query;

use PDO;

use function implode;
use function is_array;
use function is_bool;
use function is_int;
use function is_object;

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
     * @var array
     */
    protected array $store = [];

    /**
     * @param mixed $value
     * @param int   $type
     *
     * @return string
     */
    public function bindInline(mixed $value, int $type = -1): string
    {
        if (is_object($value) && $value instanceof Select) {
            return "(" . $value->getStatement() . ")";
        }

        if (is_array($value)) {
            return $this->inlineArray($value, $type);
        }

        $this->inlineCount = $this->inlineCount + 1;
        $key               = "__" . $this->inlineCount . "__";

        $this->setValue($key, $value, $type);

        return ":" . $key;
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
     * Sets a value
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $type
     */
    public function setValue(string $key, mixed $value, int $type = -1): void
    {
        $localType = $type;
        if ($localType === -1) {
            $localType = $this->getType($value);
        }

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
     * Processes an array - if passed as an `inline` parameter
     *
     * @param array $array
     * @param int   $type
     *
     * @return string
     */
    protected function inlineArray(array $data, int $type): string
    {
        $keys = [];

        foreach ($data as $value) {
            $this->inlineCount = $this->inlineCount + 1;
            $key               = "__" . $this->inlineCount . "__";

            $this->setValue($key, $value, $type);

            $keys[] = ":" . $key;
        }

        return "(" . implode(", ", $keys) . ")";
    }
}
