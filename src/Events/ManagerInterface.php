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

namespace Phalcon\Events;

use Phalcon\Contracts\Events\Manager as ManagerContract;

/**
 * Phalcon\Events\ManagerInterface
 *
 * @psalm-suppress DeprecatedInterface
 * @deprecated Will be removed in a future major release.
 *             Use {@see \Phalcon\Contracts\Events\Manager} instead.
 *
 * The manager class carries this member and the framework calls it on the
 * interface. It joins the contract in the next major; until then the tag
 * below records what all implementations provide.
 *
 * @method mixed dispatch(object $event, array<array-key, string>|string|null $name = null, object|null $source = null)
 */
interface ManagerInterface extends ManagerContract
{
}
