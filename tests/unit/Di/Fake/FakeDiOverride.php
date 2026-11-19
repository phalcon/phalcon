<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Di\Fake;

use Phalcon\Di\Di;
use Phalcon\Events\ManagerInterface;

/**
 * Overrides setInternalEventsManager() with the cphalcon signature
 */
class FakeDiOverride extends Di
{
    public function setInternalEventsManager(ManagerInterface $eventsManager)
    {
        parent::setInternalEventsManager($eventsManager);
    }
}
