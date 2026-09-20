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
use Phalcon\Contracts\Annotations\AnnotationsTypes;

use function strtolower;

/**
 * Stores the parsed annotations in memory. This adapter is the suitable
 * development/testing
 *
 * @phpstan-import-type annotations_cache from AnnotationsTypes
 * @phpstan-import-type annotations_options from AnnotationsTypes
 */
class Memory extends AbstractAdapter
{
    /**
     * The property has no initializer, so it is null until the first write.
     *
     * @phpstan-var annotations_cache|null
     */
    protected mixed $data = null;

    /**
     * @phpstan-param annotations_options $options
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * Reads parsed annotations from memory
     */
    public function read(string $key): bool | Reflection
    {
        return $this->data[strtolower($key)] ?? false;
    }

    /**
     * Writes parsed annotations to memory
     */
    public function write(string $key, Reflection $data): void
    {
        $lowercasedKey = strtolower($key);

        $this->data[$lowercasedKey] = $data;
    }
}
