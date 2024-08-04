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
use Phalcon\Tests\UnitTestCase;

final class ToJsonTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: toJson()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSupportRegistryToJson(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);

        $this->assertSame(
            json_encode($data),
            $registry->toJson()
        );

        $this->assertSame(
            json_encode($data, JSON_PRETTY_PRINT),
            $registry->toJson(JSON_PRETTY_PRINT)
        );
    }
}
