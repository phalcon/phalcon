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
 * Phalcon's local mirror of PSR-14 StoppableEventInterface.
 */
interface Stoppable
{
    /**
     * Returns true when the event must stop propagating to subsequent
     * listeners.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;
}
