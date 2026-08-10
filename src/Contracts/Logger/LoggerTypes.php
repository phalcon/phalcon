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

namespace Phalcon\Contracts\Logger;

use DateTimeZone;
use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Item;

/**
 * Central registry of the array shapes used across the Logger namespace.
 *
 * This is a type registry, not a contract. It declares no members and must
 * not be implemented; it exists only so that every shape below has a single
 * definition, imported where it is needed with a phpstan-import-type tag
 * naming this interface as the source.
 *
 * Alias names are prefixed with `logger_` because PHPStan resolves imported
 * type names per file and has no namespacing for them: the prefix is what
 * keeps generic names such as `adapter_options` from clashing with an alias
 * imported from another namespace into the same file.
 *
 * Adapter stacks are keyed by `array-key` rather than `string`: the factory
 * keys them by whatever the config used, and an integer-keyed adapter list
 * is a supported (and tested) configuration.
 *
 * The transaction queue is `array<int, Item>` and not `list<Item>`: enforcing
 * the queue limit unsets the oldest entry, which leaves a gap in the keys,
 * so the queue stops being a list on the first eviction.
 *
 * @phpstan-type logger_context array<string, mixed>
 * @phpstan-type logger_adapters array<array-key, AdapterInterface>
 * @phpstan-type logger_excluded array<array-key, bool>
 * @phpstan-type logger_levels array<int, string>
 * @phpstan-type logger_queue array<int, Item>
 * @phpstan-type logger_adapter_options array<string, mixed>
 * @phpstan-type logger_stream_options array{
 *     mode?: string,
 * }
 * @phpstan-type logger_syslog_options array{
 *     option?: int,
 *     facility?: int,
 * }
 * @phpstan-type logger_adapter_config array{
 *     adapter: string,
 *     name: string,
 *     options?: logger_adapter_options,
 * }
 * `timezone` is typed as the object, not a string: `LoggerFactory::load()`
 * hands the value straight to `newInstance(..., DateTimeZone|null $timezone)`,
 * so a string from an INI or array config would be a TypeError there.
 *
 * @phpstan-type logger_factory_config array{
 *     name: string,
 *     timezone?: DateTimeZone,
 *     options?: array{
 *         adapters?: array<array-key, logger_adapter_config>,
 *     },
 * }
 */
interface LoggerTypes
{
}
