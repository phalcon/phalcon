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

namespace Phalcon\Tests\Unit\Annotations\Reflection;

use Phalcon\Annotations\Reflection;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetReflectionDataTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Reflection :: getReflectionData()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAnnotationsReflectionGetReflectionData(): void
    {
        $reflection = new Reflection();

        $actual = $reflection->getReflectionData();
        $this->assertIsArray($actual);
        $this->assertEmpty($actual);
    }
}
