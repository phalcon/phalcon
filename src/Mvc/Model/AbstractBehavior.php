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
abstract class AbstractBehavior implements BehaviorInterface
{
    public function __construct(
        protected array $options = []
    ) {
    }

    /**
     * Acts as fallbacks when a missing method is called on the model
     *
     * @param ModelInterface $model
     * @param string         $method
     * @param array          $arguments
     *
     * @return null
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
     * @param string         $type
     * @param ModelInterface $model
     *
     * @return null
     */
    public function notify(string $type, ModelInterface $model)
    {
        return null;
    }

    /**
     * Returns the behavior options related to an event
     *
     * @param string|null $eventName
     *
     * @return array
     */
    protected function getOptions(string | null $eventName = null): array
    {
        if (null !== $eventName) {
            return $this->options[$eventName] ?? [];
        }

        return $this->options;
    }

    /**
     * Checks whether the behavior must take action on certain event
     *
     * @param string $eventName
     *
     * @return bool
     */
    protected function mustTakeAction(string $eventName): bool
    {
        return isset($this->options[$eventName]);
    }
}
