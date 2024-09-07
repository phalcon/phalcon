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

/**
 * @property Connection $connection
 * @property array      $store
 *
 * @method void processValue(string $store, array | string $data)
 */
trait OrderByTrait
{
    /**
     * Sets the `ORDER BY`
     *
     * @param array|string $orderBy
     *
     * @return static
     */
    public function orderBy(mixed $orderBy): static
    {
        $this->processValue("ORDER", $orderBy);

        return $this;
    }

    /**
     * Resets the order by
     */
    public function resetOrderBy(): static
    {
        $this->store["ORDER"] = [];

        return $this;
    }
}
