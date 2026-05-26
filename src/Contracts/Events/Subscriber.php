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

namespace Phalcon\Contracts\Events;

/**
 * Contract for event subscriber classes. A subscriber declares the events it
 * wants to listen to via a static map; Events\Manager parses the map and
 * attaches each entry as a regular listener.
 *
 * Accepted value shapes per event key:
 *
 *   'event:name' => 'methodName'
 *   'event:name' => ['methodName', priority]
 *   'event:name' => [
 *       ['methodName1'],
 *       ['methodName2', priority],
 *   ]
 */
interface Subscriber
{
    /**
     * Returns a map of event name => listener config.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array;
}
