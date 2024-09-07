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

namespace Phalcon\DataMapper\Statement\Clause\Traits\Columns;

use function array_merge;
use function is_int;

/**
 * @property array $store
 *
 * @method string indent(array $collection, string $glue = "")
 */
trait Select
{
    public function build(): string
    {
        return $this->indent($this->store['COLUMNS'], ',');
    }

    /**
     * The columns to select from. If a key is set in the array element, the
     * key will be used as the alias
     *
     * @param array $columns
     *
     * @return static
     */
    public function columns(array $columns): static
    {
        $localColumns = [];
        foreach ($columns as $key => $value) {
            if (is_int($key)) {
                $localColumns[] = $value;
            } else {
                $localColumns[] = $value . " AS " . $key;
            }
        }

        $this->store["COLUMNS"] = array_merge(
            $this->store["COLUMNS"],
            $localColumns
        );

        return $this;
    }

    /**
     * Whether the query has columns or not
     *
     * @return bool
     */
    public function hasColumns(): bool
    {
        return !empty($this->store["COLUMNS"]);
    }

    /**
     * Resets the columns
     */
    public function resetColumns(): static
    {
        $this->store["COLUMNS"] = [];

        return $this;
    }

    /**
     * Builds the columns list
     *
     * @return string
     */
    protected function buildColumns(): string
    {
        if (!$this->hasColumns()) {
            $columns = ["*"];
        } else {
            $columns = $this->store["COLUMNS"];
        }

        return $this->indent($columns, ",");
    }
}
