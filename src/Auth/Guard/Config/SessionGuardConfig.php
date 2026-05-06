<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Guard\Config;

/**
 * Placeholder config for the Session guard. The session guard has no required
 * options today; this exists for parity with TokenGuardConfig and to give the
 * locator/container something concrete to bind.
 */
class SessionGuardConfig extends AbstractGuardConfig
{
}
