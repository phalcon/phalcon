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

namespace Phalcon\Tests\Unit\Config\Adapter\Grouped;

use Phalcon\Tests\Fixtures\Traits\ConfigTrait;
use Phalcon\Tests\UnitTestCase;

final class ToArrayTest extends UnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: toArray()
     *
     * @author kjdev
     * @since  2013-07-18
     */
    public function testConfigAdapterGroupedToArray(): void
    {
        $config  = $this->getConfig('Grouped');
        $options = $this->config;

        $options['test']['property2'] = 'something-else';

        $expected = $options;
        $actual   = $config->toArray();
        $this->assertSame($expected, $actual);
    }
}
