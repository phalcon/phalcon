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

namespace Phalcon\Tests\Fixtures\Traits;

use IntegrationTester;

use function getOptionsRedis;

trait RedisTrait
{
    protected array $options = [];

    public function setUp(): void
    {
        $this->checkExtensionIsLoaded('redis');

        $this->options = getOptionsRedis();
    }
}
