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

use function apcu_fetch;
use function apcu_store;
use function strtolower;

/**
 * Stores the parsed annotations in APCu. This adapter is suitable for production
 *
 *```php
 * use Phalcon\Annotations\Adapter\Apcu;
 *
 * $annotations = new Apcu();
 *```
 *
 * @phpstan-import-type annotations_options from AnnotationsTypes
 */
class Apcu extends AbstractAdapter
{
    protected string $prefix = "";

    protected int $ttl = 172800;

    /**
     * @param array $options = [
     *                       'prefix' => 'phalcon'
     *                       'lifetime' => 3600
     *                       ]
     *
     * Phalcon\Annotations\Adapter\Apcu constructor
     *
     * @phpstan-param annotations_options $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options["prefix"])) {
            /** @var string $prefix */
            $prefix       = $options["prefix"];
            $this->prefix = $prefix;
        }

        if (isset($options["lifetime"])) {
            /** @var int $ttl */
            $ttl       = $options["lifetime"];
            $this->ttl = $ttl;
        }
    }

    /**
     * Reads parsed annotations from APCu
     */
    public function read(string $key): bool | Reflection
    {
        /** @var bool|Reflection */
        return apcu_fetch(
            strtolower(
                "_PHAN" . $this->prefix . $key
            )
        );
    }

    /**
     * Writes parsed annotations to APCu
     */
    public function write(string $key, Reflection $data): bool
    {
        /** @var bool */
        return apcu_store(
            strtolower(
                "_PHAN" . $this->prefix . $key
            ),
            $data,
            $this->ttl
        );
    }
}
