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

namespace Phalcon\Contracts\Translate;

/**
 * Central registry of the array shapes used across the Translate namespace.
 *
 * This is a type registry, not a contract. It declares no members and must
 * not be implemented; it exists only so that every shape below has a single
 * definition, imported where it is needed with a phpstan-import-type tag
 * naming this interface as the source.
 *
 * Alias names are prefixed with `translate_` because PHPStan resolves imported
 * type names per file and has no namespacing for them: the prefix is what
 * keeps generic names such as `adapter_options` from clashing with an alias
 * imported from another namespace into the same file.
 *
 * Every adapter option shape repeats the two base keys rather than
 * intersecting `translate_adapter_options` with `&`: each adapter hands its
 * own options straight to `AbstractAdapter::__construct()`, so the base keys
 * genuinely belong to every shape - and PHPStan collapses an intersection of
 * two all-optional array shapes to the first operand, which silently drops
 * the adapter's own keys.
 *
 * @phpstan-type translate_placeholders array<string, string>
 * @phpstan-type translate_data array<string, string>
 * @phpstan-type translate_adapter_options array{
 *     defaultInterpolator?: string,
 *     triggerError?: bool,
 * }
 * @phpstan-type translate_array_options array{
 *     defaultInterpolator?: string,
 *     triggerError?: bool,
 *     content?: mixed,
 * }
 * @phpstan-type translate_csv_options array{
 *     defaultInterpolator?: string,
 *     triggerError?: bool,
 *     content?: string,
 *     delimiter?: string,
 *     enclosure?: string,
 *     escape?: string,
 * }
 * @phpstan-type translate_gettext_options array{
 *     defaultInterpolator?: string,
 *     triggerError?: bool,
 *     locale?: array<array-key, string>,
 *     defaultDomain?: string,
 *     directory?: translate_data|string,
 *     category?: int,
 * }
 * @phpstan-type translate_gettext_defaults array{
 *     category: int,
 *     defaultDomain: string,
 * }
 * @phpstan-type translate_factory_config array{
 *     adapter: string,
 *     options?: array<string, mixed>,
 * }
 */
interface TranslateTypes
{
}
