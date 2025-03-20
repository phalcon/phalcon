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
use Phalcon\Http\Message\Interfaces\RequestMethodInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $route,
        public string | array $methods = [
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
        public array $converters = []
    ) {
    }
}
