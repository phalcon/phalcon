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

namespace Phalcon\DataMapper\Pdo;

/**
 * Lifecycle event names fired by the DataMapper connections through
 * Phalcon\Events\Manager. One public constant per event.
 *
 * The `before*` events are cancelable. To cancel an operation, a listener
 * must stop the event and return false:
 *
 *     $manager->attach(
 *         Events::BEFORE_PERFORM,
 *         function ($event) {
 *             $event->stop();
 *
 *             return false;
 *         }
 *     );
 *
 * Both parts are necessary. `stop()` alone abandons the queue but returns
 * the listener's own value, which the connection cannot tell apart from
 * "no listeners". `return false` alone is replaced by any later non-null
 * return while the manager's stopOnFalse mode is off, which is the default.
 * A canceled operation throws
 * Phalcon\DataMapper\Pdo\Exception\OperationCancelled.
 *
 * The `after*` events are not cancelable. The operation is complete when
 * they fire.
 *
 * There are two groups of events. The operation events - perform, exec,
 * query and the three transaction events - belong to one operation each.
 * `prepare()` has no operation events because `perform()` calls it, and
 * nested events for one logical operation give listeners two counts of the
 * same work. The connection events - connect, disconnect and connectionLost
 * - report a change of the connection state. They fire each time the state
 * changes, whichever method causes it. An automatic reconnect from any
 * method therefore reports the lost connection and the new one.
 */
class Events
{
    public const AFTER_BEGIN_TRANSACTION  = "dm:afterBeginTransaction";
    public const AFTER_COMMIT             = "dm:afterCommit";
    public const AFTER_CONNECT            = "dm:afterConnect";
    public const AFTER_DISCONNECT         = "dm:afterDisconnect";
    public const AFTER_EXEC               = "dm:afterExec";
    public const AFTER_PERFORM            = "dm:afterPerform";
    public const AFTER_QUERY              = "dm:afterQuery";
    public const AFTER_ROLLBACK           = "dm:afterRollBack";
    public const BEFORE_BEGIN_TRANSACTION = "dm:beforeBeginTransaction";
    public const BEFORE_COMMIT            = "dm:beforeCommit";
    public const BEFORE_CONNECT           = "dm:beforeConnect";
    public const BEFORE_DISCONNECT        = "dm:beforeDisconnect";
    public const BEFORE_EXEC              = "dm:beforeExec";
    public const BEFORE_PERFORM           = "dm:beforePerform";
    public const BEFORE_QUERY             = "dm:beforeQuery";
    public const BEFORE_ROLLBACK          = "dm:beforeRollBack";
    public const CONNECTION_LOST          = "dm:connectionLost";
}
