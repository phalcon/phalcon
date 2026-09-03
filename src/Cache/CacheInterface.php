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

use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Contracts\Cache\Cache as CacheContract;

/**
 * Interface for Phalcon\Cache\Cache
 *
 * @psalm-suppress DeprecatedInterface
 * @deprecated Will be removed in a future major release.
 *             Use {@see \Phalcon\Contracts\Cache\Cache} instead.
 *
 * The cache class carries this member and the framework calls it on the
 * interface. It joins the contract in the next major; until then the tag
 * below records what all implementations provide.
 *
 * @method AdapterInterface getAdapter()
 */
interface CacheInterface extends CacheContract
{
}
