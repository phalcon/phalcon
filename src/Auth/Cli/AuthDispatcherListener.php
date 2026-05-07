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

namespace Phalcon\Auth\Cli;

use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Manager;
use Phalcon\Cli\Dispatcher;
use Phalcon\Events\Event;

class AuthDispatcherListener
{
    public function __construct(protected Manager $manager)
    {
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

        $task = (string) $dispatcher->getActionName();
        if ($access->isAllowed($task)) {
            return true;
        }

        throw Exception::accessDenied('task', $task);
    }
}
