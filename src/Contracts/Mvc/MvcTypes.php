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

namespace Phalcon\Contracts\Mvc;

/**
 * Central registry of the array shapes used across the Mvc namespace.
 *
 * This is a type registry, not a contract. It declares no members and must
 * not be implemented; it exists only so that every shape below has a single
 * definition, imported where it is needed with a phpstan-import-type tag
 * naming this interface as the source.
 *
 * Alias names are prefixed with `mvc_` because PHPStan resolves imported
 * type names per file and has no namespacing for them: the prefix is what
 * keeps generic names such as `model_find_parameters` from clashing with an
 * alias imported from another namespace into the same file.
 *
 * @phpstan-type mvc_model_find_parameters array{
 *     conditions: string,
 *     bind: array<string, mixed>,
 * }
 */
interface MvcTypes
{
}
