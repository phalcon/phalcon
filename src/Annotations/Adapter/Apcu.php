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

use Phalcon\Annotations\Parser\Reflection;
use Phalcon\Storage\Adapter\Apcu as StorageApcu;

/**
 * Stores the parsed annotations in apcu.
 */
class Apcu extends StorageApcu implements AdapterInterface
{
    /**
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return Reflection|mixed
     */
    public function get(string $key, mixed $defaultValue = null): mixed
    {
        return parent::get($key, $defaultValue);
    }
}
