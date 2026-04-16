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
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Fake component that exposes fireManagerEvent() and firePsrEvent()
 * for coverage of EventsAwareTrait L69-72 and L80-81.
 */
class ComponentFireManager
{
    use EventsAwareTrait;

    public function callFireManagerEvent(string $eventName, mixed $data = null): mixed
    {
        return $this->fireManagerEvent($eventName, $data);
    }

    public function callFirePsrEvent(PsrEventInterface $event, ?string $name = null): mixed
    {
        return $this->firePsrEvent($event, $name);
    }
}
