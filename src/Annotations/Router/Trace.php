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
use Phalcon\Http\Message\RequestMethodInterface;

/**
 * Marks a method as a TRACE route. It is the attribute form of `@Trace`.
 *
 * @phpstan-import-type annotations_route_before_match from AnnotationsTypes
 * @phpstan-import-type annotations_route_converters from AnnotationsTypes
 * @phpstan-import-type annotations_route_paths from AnnotationsTypes
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Trace extends Route
{
    /**
     * @phpstan-param annotations_route_paths        $paths
     * @phpstan-param annotations_route_converters   $converters
     * @phpstan-param annotations_route_before_match $beforeMatch
     */
    public function __construct(
        string $route,
        string | null $name = null,
        array $paths = [],
        array $converters = [],
        array | string | null $beforeMatch = null
    ) {
        parent::__construct(
            $route,
            RequestMethodInterface::METHOD_TRACE,
            $name,
            $paths,
            $converters,
            $beforeMatch
        );
    }
}
