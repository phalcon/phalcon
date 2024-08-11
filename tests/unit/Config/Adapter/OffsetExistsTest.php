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
use Phalcon\Tests\AbstractUnitTestCase;

final class OffsetExistsTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: offsetExists()
     *
     * @dataProvider providerConfigAdapters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testConfigAdapterOffsetExists(
        string $adapter
    ): void {
        $config = $this->getConfig($adapter);

        $actual = $config->offsetExists('models');
        $this->assertTrue($actual);
    }
}
