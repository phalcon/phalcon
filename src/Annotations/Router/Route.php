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

namespace Phalcon\Annotations\Router;

use Attribute;
use Phalcon\Contracts\Annotations\AnnotationsTypes;

/**
 * Marks a method as a route. It is the attribute form of `@Route`.
 *
 * The annotations service never makes an instance of this class. It reads the
 * arguments with ReflectionAttribute::getArguments(). The class gives the
 * name, the targets and the signature that an IDE and a static analyzer read.
 *
 * @phpstan-import-type annotations_route_before_match from AnnotationsTypes
 * @phpstan-import-type annotations_route_converters from AnnotationsTypes
 * @phpstan-import-type annotations_route_methods from AnnotationsTypes
 * @phpstan-import-type annotations_route_paths from AnnotationsTypes
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @phpstan-param annotations_route_methods      $methods
     * @phpstan-param annotations_route_paths        $paths
     * @phpstan-param annotations_route_converters   $converters
     * @phpstan-param annotations_route_before_match $beforeMatch
     */
    public function __construct(
        public string $route,
        public array | string | null $methods = null,
        public string | null $name = null,
        public array $paths = [],
        public array $converters = [],
        public array | string | null $beforeMatch = null
    ) {
    }
}
