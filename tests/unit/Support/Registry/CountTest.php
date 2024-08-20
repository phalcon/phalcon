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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use PHPUnit\Framework\Attributes\Test;

final class CountTest extends AbstractRegistryTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: count()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testSupportRegistryCount(): void
    {
        $data = $this->getData();
        $registry = new Registry($data);

        $expected = 3;
        $actual  = $registry->toArray();
        $this->assertCount($expected, $actual);

        $expected = 3;
        $actual   = $registry->count();
        $this->assertSame($expected, $actual);
    }
}
