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

namespace Phalcon\Tests\Fixtures\Support\Version;

use Phalcon\Support\Version;

/**
 * Fixture for alpha version
 */
class VersionAlphaFixture extends Version
{
    protected function getVersion(): array
    {
        return [5, 0, 0, 1, 1];
    }
}
