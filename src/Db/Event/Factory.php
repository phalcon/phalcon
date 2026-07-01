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

namespace Phalcon\Db\Event;

use Phalcon\Events\PsrEventInterface;
use Phalcon\Events\UnknownEventTypeException;
use Phalcon\Mvc\Model;

class Factory
{
    public function create(string $eventName, Model $model): ?PsrEventInterface
    {
        try {
            $className = ModelEventNameEnum::getEventClass($eventName);

            return new $className($model);
        } catch (UnknownEventTypeException) {
            return null;
        }
    }
}
