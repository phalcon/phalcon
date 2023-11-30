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
use Phalcon\Support\Traits\PhpApcuTrait;

use function strtolower;

/**
 * Stores the parsed annotations in APCu. This adapter is suitable for
 * production
 *
 *```php
 * use Phalcon\Annotations\Adapter\Apcu;
 *
 * $annotations = new Apcu();
 *```
 */
class Apcu extends AbstractAdapter
{
    use PhpApcuTrait;

    /**
     * @var string
     */
    protected string $prefix = "";

    /**
     * @var int
     */
    protected int $ttl = 172800;

    /**
     * Constructor
     *
     * @param array $options = [
     *                       'prefix' => 'phalcon'
     *                       'lifetime' => 3600
     *                       ]
     */
    public function __construct(array $options = [])
    {
        $this->prefix = $options["prefix"] ?? $this->prefix;
        $this->ttl    = $options["lifetime"] ?? $this->ttl;
    }

    /**
     * Reads parsed annotations from APCu
     *
     * @param string $key
     *
     * @return Reflection|bool
     */
    public function read(string $key): Reflection | bool
    {
        return $this->phpApcuFetch(
            strtolower("_PHAN" . $this->prefix . $key)
        );
    }

    /**
     * Writes parsed annotations to APCu
     *
     * @param string     $key
     * @param Reflection $data
     *
     * @return bool
     */
    public function write(string $key, Reflection $data): bool
    {
        return $this->phpApcuStore(
            strtolower("_PHAN" . $this->prefix . $key),
            $data,
            $this->ttl
        );
    }
}
