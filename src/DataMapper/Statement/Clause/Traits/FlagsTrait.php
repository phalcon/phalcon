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

use function array_keys;
use function implode;

/**
 * @property array $store
 */
trait FlagsTrait
{
    /**
     * Resets the flags
     */
    public function resetFlags(): void
    {
        $this->store["FLAGS"] = [];
    }

    /**
     * Sets a flag for the query such as "DISTINCT"
     *
     * @param string $flag
     * @param bool   $enable
     */
    public function setFlag(string $flag, bool $enable = true): void
    {
        if (true === $enable) {
            $this->store["FLAGS"][$flag] = true;
        } else {
            unset($this->store["FLAGS"][$flag]);
        }
    }

    /**
     * Builds the flags statement(s)
     *
     * @return string
     */
    protected function buildFlags()
    {
        if (empty($this->store["FLAGS"])) {
            return "";
        }

        return " " . implode(" ", array_keys($this->store["FLAGS"]));
    }
}
