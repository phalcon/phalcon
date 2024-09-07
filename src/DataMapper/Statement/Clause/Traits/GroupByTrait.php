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
trait GroupByTrait
{
    /**
     * Sets the `GROUP BY`
     *
     * @param array|string $groupBy
     *
     * @return static
     */
    public function groupBy(array | string $groupBy): static
    {
        $this->processValue("GROUP", $groupBy);

        return $this;
    }

    /**
     * Resets the group by
     */
    public function resetGroupBy(): static
    {
        $this->store["GROUP"] = [];

        return $this;
    }

}
