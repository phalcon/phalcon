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
use Phalcon\Tests\AbstractUnitTestCase;

final class PathTest extends AbstractUnitTestCase
{
    use ConfigTrait;

    /**
     * Tests Phalcon\Config\Adapter\Grouped :: path()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testConfigAdapterGroupedPath(): void
    {
        $config = $this->getConfig('Grouped');

        $expected = 2;
        $actual   = $config->path('test');
        $this->assertCount($expected, $actual);


        $expected = 'something-else';
        $actual   = $config->path('test.property2');
        $this->assertSame($expected, $actual);
    }
}
