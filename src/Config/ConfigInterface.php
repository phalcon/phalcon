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

namespace Phalcon\Config;

use Phalcon\Support\Collection\CollectionInterface;

/**
 * Phalcon\Config\ConfigInterface
 *
 * Interface for Phalcon\Config class
 */
interface ConfigInterface extends CollectionInterface
{
    /**
     * @return string
     */
    public function getPathDelimiter(): string;

    /**
     * @param array|ConfigInterface $toMerge
     *
     * @return ConfigInterface
     */
    public function merge(array | ConfigInterface $toMerge): ConfigInterface;

    /**
     * @param string      $path
     * @param mixed|null  $defaultValue
     * @param string|null $delimiter
     *
     * @return mixed
     */
    public function path(
        string $path,
        mixed $defaultValue = null,
        string | null $delimiter = null
    );

    /**
     * @param string $delimiter
     *
     * @return ConfigInterface
     */
    public function setPathDelimiter(string $delimiter): ConfigInterface;
}
