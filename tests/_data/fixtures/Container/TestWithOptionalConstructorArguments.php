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

class TestWithOptionalConstructorArguments
{
    public array $three;

    public function __construct(
        public string $one,
        public ?string $two = null,
        string ...$three
    ) {
        $this->three = $three;
    }
}
