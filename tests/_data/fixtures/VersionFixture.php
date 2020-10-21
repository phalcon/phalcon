<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Fixtures;

use Phalcon\Version\Version;

class VersionFixture extends Version
{
    /**
     * @return int[]
     */
    protected static function getVersion(): array
    {
        return [5, 0, 0, 1, 1];
    }
}
