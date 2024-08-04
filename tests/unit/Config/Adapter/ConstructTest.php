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

namespace Phalcon\Tests\Unit\Config\Adapter;

use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    use ConfigTrait;

    /**
     * @return array[]
     */
    public static function providerAdapters(): array
    {
        return [
            [
                '',
            ],
            [
                'Json',
            ],
            [
                'Php',
            ],
            [
                'Yaml',
            ],
        ];
    }

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: toArray()
     *
     * @dataProvider providerAdapters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testConfigAdapterToArray(
        string $adapter
    ): void {
        $config = $this->getConfig($adapter);

        $this->compareConfig($this->config, $config);
    }
}
