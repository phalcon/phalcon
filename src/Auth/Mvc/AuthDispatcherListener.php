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

namespace Phalcon\Auth\Mvc;

use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Manager;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

/**
 * Listener that enforces the active Phalcon\Auth access gate on each MVC
 * dispatch. Attach to the events manager:
 *
 *   $eventsManager->attach('dispatch', new AuthDispatcherListener($manager));
 *
 * No-op when no active access has been set on the manager.
 */
class AuthDispatcherListener
{
    public function __construct(
        protected Manager $manager
    ) {
    }

    /**
     * @param Event      $event
     * @param Dispatcher $dispatcher
     *
     * @return bool
     * @throws Exception
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher): bool
    {
        $access = $this->manager->getAccess();
        if ($access === null) {
            return true;
        }

        $action = (string) $dispatcher->getActionName();
        if ($access->isAllowed($action)) {
            return true;
        }

        $target = $access->redirectTo();
        if ($target !== null) {
            $dispatcher->forward($target);

            return false;
        }

        throw Exception::accessDenied('action', $action);
    }
}
