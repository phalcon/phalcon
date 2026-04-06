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

namespace Phalcon\Cache;

use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

/**
 * Interface for Phalcon\Cache\Cache
 * Extends PSR-16 CacheInterface for compatibility.
 */
interface CacheInterface extends PsrCacheInterface
{
}

