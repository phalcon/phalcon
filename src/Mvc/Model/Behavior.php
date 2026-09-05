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

namespace Phalcon\Mvc\Model;

use Phalcon\Mvc\ModelInterface;

/**
 * This is an optional base class for ORM behaviors
 */
abstract class Behavior implements BehaviorInterface
{
    /**
     * Phalcon\Mvc\Model\Behavior
     *
     * @phpstan-param array<string, mixed> $options
     */
    public function __construct(
        protected array $options = []
    ) {
    }

    /**
     * Acts as fallbacks when a missing method is called on the model
     *
     * @return null
     *
     * @phpstan-param array<array-key, mixed> $arguments
     */
    public function missingMethod(
        ModelInterface $model,
        string $method,
        array $arguments = []
    ) {
        return null;
    }

    /**
     * This method receives the notifications from the EventsManager
     *
     * @return null
     *
     * @phpstan-return mixed
     */
    public function notify(string $type, ModelInterface $model)
    {
        return null;
    }

    /**
     * Returns the behavior options related to an event
     *
     * @return mixed
     *
     * @phpstan-return array<string, mixed>|mixed
     */
    protected function getOptions(string | null $eventName = null)
    {
        if (null !== $eventName) {
            return $this->options[$eventName] ?? null;
        }

        return $this->options;
    }

    /**
     * Checks whether the behavior must take action on certain event
     */
    protected function mustTakeAction(string $eventName): bool
    {
        return isset($this->options[$eventName]);
    }
}
