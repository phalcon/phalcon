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

namespace Phalcon\Contracts\Queue;

/**
 * Central registry of the array shapes used across the Queue namespace.
 *
 * This is a type registry, not a contract. It declares no members and must
 * not be implemented; it exists only so that every shape below has a single
 * definition, imported where it is needed with a phpstan-import-type tag
 * naming this interface as the source.
 *
 * Alias names are prefixed with `queue_` because PHPStan resolves imported
 * type names per file and has no namespacing for them: the prefix is what
 * keeps generic names such as `connection_options` from clashing with an
 * alias imported from another namespace into the same file.
 *
 * @phpstan-type queue_message_headers array<string, mixed>
 * @phpstan-type queue_message_properties array<string, mixed>
 * @phpstan-type queue_message_envelope array{
 *     body: string,
 *     properties: queue_message_properties,
 *     headers: queue_message_headers,
 * }
 * @phpstan-type queue_connection_options array<string, mixed>
 * @phpstan-type queue_beanstalk_options array{
 *     host?: string,
 *     port?: int,
 *     persistent?: bool,
 *     ttr?: int,
 *     pollInterval?: int,
 * }
 * @phpstan-type queue_redis_options array{
 *     host?: string,
 *     port?: int,
 *     timeout?: int,
 *     persistent?: bool,
 *     persistentId?: string,
 *     auth?: string|array{0: string, 1: string},
 *     index?: int,
 *     prefix?: string,
 *     pollInterval?: int,
 * }
 * @phpstan-type queue_stream_options array{
 *     storageDir?: string,
 *     pollInterval?: int,
 * }
 * @phpstan-type queue_subscriptions array<string, array{0: Consumer, 1: callable}>
 * @phpstan-type queue_beanstalk_status list<string>
 * @phpstan-type queue_beanstalk_job array{0: string, 1: false|string}
 */
interface QueueTypes
{
}
