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

namespace Phalcon\Tests\Unit\Events\Fake;

use Phalcon\Events\PsrEventInterface;

/**
 * Minimal PSR event object for testing EventsAwareTrait::firePsrEvent().
 */
class PsrEventObject implements PsrEventInterface
{
}
