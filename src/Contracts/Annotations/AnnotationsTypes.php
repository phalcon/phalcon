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

namespace Phalcon\Contracts\Annotations;

use Phalcon\Annotations\Parser\Annotation;
use Phalcon\Annotations\Parser\Collection;
use Phalcon\Annotations\Parser\Reflection;
use ReflectionAttribute;

/**
 * Central registry of the array shapes used across the Annotations namespace.
 *
 * This is a type registry, not a contract. It declares no members and must
 * not be implemented; it exists only so that every shape below has a single
 * definition, imported where it is needed with a phpstan-import-type tag
 * naming this interface as the source.
 *
 * Alias names are prefixed with `annotations_` because PHPStan resolves
 * imported type names per file and has no namespacing for them: the prefix is
 * what keeps generic names such as `arguments` or `list` from clashing with an
 * alias imported from another namespace into the same file.
 *
 * The list is alphabetical, with one exception: an alias that another alias
 * names must be defined before it.
 *
 * @phpstan-type annotations_arguments array<array-key, mixed>
 * @phpstan-type annotations_attributes array<string, Reflection>
 * @phpstan-type annotations_collection_map array<string, Collection>
 * @phpstan-type annotations_list list<Annotation>
 * @phpstan-type annotations_reflection_attributes array<array-key, ReflectionAttribute<object>>
 * @phpstan-type annotations_reflection_data array{
 *     class?: Collection,
 *     constants?: annotations_collection_map,
 *     methods?: annotations_collection_map,
 *     properties?: annotations_collection_map,
 * }
 * @phpstan-type annotations_route_before_match array<array-key, mixed>|string|null
 * @phpstan-type annotations_route_converters array<array-key, mixed>
 * @phpstan-type annotations_route_methods array<array-key, string>|string
 * @phpstan-type annotations_route_paths array<array-key, mixed>
 * @phpstan-type annotations_route_params array{
 *     0?: string,
 *     route?: string,
 *     methods?: annotations_route_methods,
 *     name?: string|null,
 *     paths?: annotations_route_paths,
 *     converters?: annotations_route_converters,
 *     beforeMatch?: annotations_route_before_match,
 * }
 */
interface AnnotationsTypes
{
}
