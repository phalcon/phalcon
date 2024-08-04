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

namespace Phalcon\Tests\Fixtures\Container;

use stdClass;

class TestWithDefaultConstructorParameters
{
    public function __construct(
        public stdClass $first,
        public TestWithInterface $second,
        public string $third = 'defaultValue'
    ) {
    }
}
