<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this component has been inspired by the queue-interop and
 * enqueue projects.
 *
 * @link    https://github.com/queue-interop/queue-interop
 * @license https://github.com/queue-interop/queue-interop/blob/master/LICENSE
 *
 * @link    https://github.com/php-enqueue/enqueue-dev
 * @license https://github.com/php-enqueue/enqueue-dev/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Queue\Consumer;

/**
 * Lifecycle event names fired by the queue consumer through
 * Phalcon\Events\Manager. One public constant per event.
 */
class Events
{
    public const AFTER_END           = "queue:afterEnd";
    public const AFTER_PROCESS        = "queue:afterProcess";
    public const AFTER_RECEIVE        = "queue:afterReceive";
    public const BEFORE_PROCESS       = "queue:beforeProcess";
    public const BEFORE_RECEIVE       = "queue:beforeReceive";
    public const BEFORE_START         = "queue:beforeStart";
    public const PROCESSOR_EXCEPTION  = "queue:processorException";
}
