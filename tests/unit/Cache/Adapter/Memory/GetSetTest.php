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

namespace Phalcon\Tests\Unit\Cache\Adapter\Memory;

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use stdClass;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\Memory :: get()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @throws HelperException
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterMemoryGetSet(
        mixed $value
    ): void {
        $serializer = new SerializerFactory();
        $adapter    = new Memory($serializer);

        $key = uniqid();

        $result = $adapter->set($key, $value);
        $this->assertTrue($result);

        $expected = $value;
        $actual   = $adapter->get($key);
        $this->assertEquals($expected, $actual);
    }

    public static function getExamples(): array
    {
        return [
            [
                'random string',
            ],
            [
                123456,
            ],
            [
                123.456,
            ],
            [
                true,
            ],
            [
                new stdClass(),
            ],
        ];
    }
}
