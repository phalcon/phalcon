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

use function array_merge;
use function is_string;

/**
 * @property array $store
 *
 * @method string indent(array $collection, string $glue = "")
 */
trait ConditionTrait
{
    /**
     * Appends a conditional
     *
     * @param string     $store
     * @param string     $andor
     * @param string     $condition
     * @param mixed|null $value
     * @param int        $type
     */
    protected function addCondition(
        string $store,
        string $andor,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): void {
        if (!empty($value)) {
            $condition .= $this->bindInline($value, $type);
        }

        if (empty($this->store[$store])) {
            $andor = "";
        }

        $this->store[$store][] = $andor . $condition;
    }

    /**
     * Concatenates a conditional
     *
     * @param string $store
     * @param string $condition
     * @param mixed  $value
     * @param int    $type
     */
    protected function appendCondition(
        string $store,
        string $condition,
        mixed $value = null,
        int $type = -1
    ): void {
        if (!empty($value)) {
            $condition .= $this->bindInline($value, $type);
        }

        if (empty($this->store[$store])) {
            $this->store[$store][] = "";
        }

        $key = array_key_last($this->store[$store]);

        $this->store[$store][$key] .= $condition;
    }

    /**
     * Builds a `BY` list
     *
     * @param string $type
     *
     * @return string
     */
    protected function buildBy(string $type): string
    {
        if (empty($this->store[$type])) {
            return "";
        }

        return " " . $type . " BY"
            . $this->indent($this->store[$type], ",");
    }

    /**
     * Builds the conditional string
     *
     * @param string $type
     *
     * @return string
     */
    protected function buildCondition(string $type): string
    {
        if (empty($this->store[$type])) {
            return "";
        }

        return " " . $type
            . $this->indent($this->store[$type]);
    }

    /**
     * Processes a value (array or string) and merges it with the store
     *
     * @param string       $store
     * @param array|string $data
     */
    protected function processValue(string $store, array | string $data): void
    {
        if (is_string($data)) {
            $data = [$data];
        }

        $this->store[$store] = array_merge(
            $this->store[$store],
            $data
        );
    }
}
