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
 * @phpstan-import-type annotations_route_params from AnnotationsTypes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Options extends Route
{
    /**
     * @param mixed ...$params
     */
    public function __construct(...$params)
    {
        $params['methods'] = RequestMethodInterface::METHOD_OPTIONS;

        /** @var annotations_route_params $params */
        parent::__construct(...$params);
    }
}
