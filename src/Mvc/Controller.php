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

namespace Phalcon\Mvc;

use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;

use function method_exists;

/**
 * Every application controller should extend this class that encapsulates all
 * the controller functionality
 *
 * The controllers provide the “flow” between models and views. Controllers are
 * responsible for processing the incoming requests from the web browser,
 * interrogating the models for data, and passing that data on to the views for
 * presentation.
 *
 *```php
 * <?php
 *
 * class PeopleController extends \Phalcon\Mvc\Controller
 * {
 *     // This action will be executed by default
 *     public function indexAction()
 *     {
 *
 *     }
 *
 *     public function findAction()
 *     {
 *
 *     }
 *
 *     public function saveAction()
 *     {
 *         // Forwards flow to the index action
 *         return $this->dispatcher->forward(
 *             [
 *                 "controller" => "people",
 *                 "action"     => "index",
 *             ]
 *         );
 *     }
 * }
 *```
 */
abstract class Controller extends Injectable implements ControllerInterface, EventsAwareInterface
{
    /**
     * Phalcon\Mvc\Controller constructor
     */
    final public function __construct()
    {
        if (method_exists($this, "onConstruct")) {
            $this->onConstruct();
        }
    }

    /**
     * Returns the internal event manager
     *
     * The property is not declared, as in cphalcon: a read resolves the
     * "eventsManager" service of the DI container, until a manager is set.
     */
    public function getEventsManager(): ManagerInterface | null
    {
        /** @var ManagerInterface|null $eventsManager */
        $eventsManager = $this->{"eventsManager"};

        return $eventsManager;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->{"eventsManager"} = $eventsManager;
    }

    /**
     * Helper method to fire an event
     */
    protected function fireManagerEvent(
        string $eventName,
        mixed $data = null,
        bool $cancellable = true
    ): mixed {
        /** @var ManagerInterface|null $eventsManager */
        $eventsManager = $this->{"eventsManager"};

        if (null !== $eventsManager) {
            return $eventsManager->fire($eventName, $this, $data, $cancellable);
        }

        return true;
    }
}
