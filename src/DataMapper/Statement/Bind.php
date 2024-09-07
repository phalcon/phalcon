<?php

/**
 * $this file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 *
 * Implementation of $this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use Atlas\Statement\Statement;
use PDO;

use function implode;
use function is_array;
use function is_bool;
use function is_int;

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
     * @var array
     */
    protected array $store = [];

    public function __construct()
    {
        $this->incrementInstanceCount();
    }

    public function __clone()
    {
        $this->incrementInstanceCount();
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->store;
    }

    /**
     * @param mixed    $value
     * @param int|null $type
     *
     * @return string
     */
    public function inline(mixed $value, ?int $type = null): string
    {
        if ($value instanceof Statement) {
            $this->store += $value->getBindValueObjects();

            return '(' . $value->getQueryString() . ')';
        }

        if (is_array($value)) {
            return $this->inlineArray($value, $type);
        }

        $key = $this->inlineValue($value, $type);

        return ':' . $key;
    }

    /**
     * @param array $values
     *
     * @return void
     */
    public function merge(array $values): void
    {
        $this->store += $values;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->inlineCount = 0;
        $this->store       = [];
    }

    /**
     * @param string   $key
     * @param mixed    $value
     * @param int|null $type
     *
     * @return void
     */
    public function value(string $key, mixed $value, ?int $type = null): void
    {
        $localType = $type;
        if ($localType === -1) {
            $localType = $this->getType($value);
        }

        $this->store[$key] = [$value, $localType];


    }

    /**
     * @param array    $values
     * @param int|null $type
     *
     * @return void
     */
    public function values(array $values, ?int $type = null): void
    {
        foreach ($values as $key => $value) {
            $this->value($key, $value, $type);
        }
    }

    protected function incrementInstanceCount(): void
    {
        static::$instanceCount++;
        $this->inlinePrefix = static::$instanceCount;
    }

    /**
     * @param array    $array
     * @param int|null $type
     *
     * @return string
     */
    protected function inlineArray(array $array, ?int $type): string
    {
        $keys = [];

        foreach ($array as $value) {
            $key    = $this->inlineValue($value, $type);
            $keys[] = ':' . $key;
        }

        return '(' . implode(', ', $keys) . ')';
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
     * @param mixed    $value
     * @param int|null $type
     *
     * @return string
     */
    protected function inlineValue(mixed $value, ?int $type): string
    {
        $this->inlineCount++;
        $key = "_{$this->inlinePrefix}_{$this->inlineCount}_";
        $this->value($key, $value, $type);

        return $key;
    }
}
