<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc\Model\MetaData\Adapter;

use Phalcon\Mvc\Model\MetaData;

/**
 * Stores model meta-data in memory. Data will be erased when the request
 * finishes
 */
class Memory extends MetaData
{
    /**
     * Reads the meta-data from temporal memory
     */
    public function read(?string $key): array | null
    {
        return null;
    }

    /**
     * Writes the meta-data to temporal memory
     */
    public function write(?string $key, array $data): void
    {
    }
}
