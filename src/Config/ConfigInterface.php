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

use Phalcon\Contracts\Config\ConfigTypes;
use Phalcon\Support\Collection\CollectionInterface;

/**
 * Phalcon\Config\ConfigInterface
 *
 * Interface for Phalcon\Config\Config class
 *
 * @extends CollectionInterface<mixed>
 *
 * @phpstan-import-type config_data from ConfigTypes
 */
interface ConfigInterface extends CollectionInterface
{
    public function getPathDelimiter(): string;

    /**
     * @phpstan-param config_data|ConfigInterface $toMerge
     */
    public function merge(mixed $toMerge): ConfigInterface;

    public function path(
        string $path,
        mixed $defaultValue = null,
        string | null $delimiter = null
    ): mixed;

    public function setPathDelimiter(string | null $delimiter = null): ConfigInterface;
}
