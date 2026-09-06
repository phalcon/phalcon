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
 * @phpstan-import-type annotations_route_before_match from AnnotationsTypes
 * @phpstan-import-type annotations_route_converters from AnnotationsTypes
 * @phpstan-import-type annotations_route_methods from AnnotationsTypes
 * @phpstan-import-type annotations_route_paths from AnnotationsTypes
 */
#[Attribute(Attribute::TARGET_METHOD)]
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
        public array | string $methods = [
            RequestMethodInterface::METHOD_CONNECT,
            RequestMethodInterface::METHOD_GET,
            RequestMethodInterface::METHOD_DELETE,
            RequestMethodInterface::METHOD_HEAD,
            RequestMethodInterface::METHOD_OPTIONS,
            RequestMethodInterface::METHOD_PATCH,
            RequestMethodInterface::METHOD_POST,
            RequestMethodInterface::METHOD_PURGE,
            RequestMethodInterface::METHOD_PUT,
            RequestMethodInterface::METHOD_TRACE,
        ],
        public string | null $name = null,
        public array $paths = [],
        public array $converters = [],
        public array | string | null $beforeMatch = null
    ) {
    }
}
