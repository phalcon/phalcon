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

namespace Phalcon\Auth\Micro;

use Phalcon\Auth\AbstractAuthDispatcherListener;
use Phalcon\Auth\Exception;
use Phalcon\Contracts\Auth\Manager;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;

/**
 * Listener that enforces the active Phalcon\Auth access gate on each Micro
 * route execution. Attach to the events manager:
 *
 *   $eventsManager->attach('micro', new AuthMicroListener($manager));
 *   $app->setEventsManager($eventsManager);
 *
 * The action name is the matched route's name, falling back to the route
 * pattern when the route is unnamed. The ACL component is the configured
 * component name (default 'Micro'). redirectTo() is ignored - Micro has no
 * forward mechanism.
 *
 * No-op when no active access has been set on the manager.
 */
class AuthMicroListener extends AbstractAuthDispatcherListener
{
    public function __construct(
        Manager $manager,
        protected string $componentName = 'Micro'
    ) {
        parent::__construct($manager);
    }

    /**
     * @throws Exception
     */
    public function beforeExecuteRoute(Event $event, Micro $application): bool
    {
        $router = $application->getRouter();
        $route  = $router->getMatchedRoute();

        if ($route === null) {
            return true;
        }

        $actionName = $route->getName();
        if ($actionName === null || $actionName === '') {
            $actionName = $route->getPattern();
        }

        return $this->enforce(
            $actionName,
            [
                'handler' => $this->componentName,
                'params'  => $router->getParams(),
            ]
        );
    }

    protected function getActionType(): string
    {
        return 'route';
    }
}
