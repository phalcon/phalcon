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

namespace Phalcon\Tests\Fixtures\Mvc;

use Phalcon\Mvc\Router;

class RouterFixture extends Router
{
    public function protectedExtractRealUri(string $uri): string
    {
        return $this->extractRealUri($uri);
    }
}
