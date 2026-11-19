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

namespace Phalcon\Tests\Unit\Encryption\Fake;

use Phalcon\Encryption\Security\JWT\Builder;

/**
 * Overrides setClaim() with the cphalcon signature
 */
class FakeBuilderOverride extends Builder
{
    protected function setClaim(string $name, $value): Builder
    {
        parent::setClaim($name, $value);

        return $this;
    }
}
