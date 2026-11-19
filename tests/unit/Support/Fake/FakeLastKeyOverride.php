<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Support\Fake;

use Phalcon\Support\Helper\Arr\LastKey;

/**
 * Overrides __invoke() with the cphalcon signature
 */
class FakeLastKeyOverride extends LastKey
{
    public function __invoke(array $collection, $method = null)
    {
        return parent::__invoke($collection, $method);
    }
}
