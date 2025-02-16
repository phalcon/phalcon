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
class Post extends Route
{
    public function __construct(...$params)
    {
        $params['methods'] = RequestMethodInterface::METHOD_POST;
        parent::__construct(...$params);
    }
}
