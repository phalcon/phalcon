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

use Phalcon\Config\Exception;
use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\UnitTestCase;

final class MergeTest extends UnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: merge()
     *
     * @dataProvider providerConfigAdapters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testConfigAdapterMerge(
        string $adapter
    ): void {
        $config = $this->getConfig($adapter);
        $this->expectExceptionMessage(Exception::class);
        $this->expectExceptionMessage('Invalid data type for merge.');

        $config->merge(false);
    }
}
