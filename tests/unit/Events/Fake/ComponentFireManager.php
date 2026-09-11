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

use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Fake component that exposes fireManagerEvent() for coverage.
 */
class ComponentFireManager
{
    use EventsAwareTrait;

    public function callFireManagerEvent(string $eventName, mixed $data = null): mixed
    {
        return $this->fireManagerEvent($eventName, $data);
    }
}
