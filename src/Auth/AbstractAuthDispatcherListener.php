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

namespace Phalcon\Auth;

use Phalcon\Auth\Exceptions\AccessDenied;
use Phalcon\Contracts\Auth\Manager;

/**
 * Shared enforcement algorithm for the Cli and Mvc auth dispatcher
 * listeners. The dispatcher-specific subclass provides only the action
 * name from its typed dispatcher, the action-kind label used in the
 * access-denied exception, and (Mvc only) a forward handler for
 * Access::redirectTo().
 */
abstract class AbstractAuthDispatcherListener
{
    public function __construct(protected Manager $manager)
    {
    }

    /**
     * Returns the kind label used by AccessDenied (e.g. 'task',
     * 'action').
     */
    abstract protected function getActionType(): string;

    /**
     * Runs the access check for the given action name. Returns true when
     * the dispatch should proceed, false when a forward was issued, and
     * throws when access is denied without a redirect target.
     *
     * @phpstan-param (callable(array<string, mixed>): void)|null $forwardHandler
     *
     * @throws Exception
     */
    protected function enforce(string $actionName, ?callable $forwardHandler = null): bool
    {
        $access = $this->manager->getAccess();
        if ($access === null) {
            return true;
        }

        if ($access->isAllowed($actionName)) {
            return true;
        }

        if ($forwardHandler !== null) {
            $target = $access->redirectTo();
            if ($target !== null) {
                $forwardHandler($target);

                return false;
            }
        }

        throw new AccessDenied($this->getActionType(), $actionName);
    }
}
