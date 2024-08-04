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

namespace Phalcon\Tests\Unit\Di\Service;

use Phalcon\Di\Service;
use Phalcon\Html\Escaper;
use Phalcon\Support\Collection;
use Phalcon\Tests\UnitTestCase;

class GetSetDefinitionTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Di\Service :: setDefinition()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiServiceGetSetDefinition(): void
    {
        $service = new Service(Escaper::class, false);

        $expected = Escaper::class;
        $actual   = $service->getDefinition();
        $this->assertSame($expected, $actual);

        $service->setDefinition(Collection::class);

        $expected = Collection::class;
        $actual   = $service->getDefinition();
        $this->assertSame($expected, $actual);
    }
}
