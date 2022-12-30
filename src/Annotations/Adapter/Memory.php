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

namespace Phalcon\Annotations\Adapter;

use Phalcon\Annotations\Reflection;

use function strtolower;

/**
 * Stores the parsed annotations in memory. This adapter is the suitable
 * development/testing
 */
class Memory extends AbstractAdapter
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Reads parsed annotations from memory
     *
     * @param string $key
     *
     * @return Reflection|bool
     */
    public function read(string $key): Reflection|bool
    {
        return $this->data[strtolower($key)] ?? false;
    }

    /**
     * Writes parsed annotations to memory
     */
    public function write(string $key, Reflection $data): void
    {
        $this->data[strtolower($key)] = $data;
    }
}
