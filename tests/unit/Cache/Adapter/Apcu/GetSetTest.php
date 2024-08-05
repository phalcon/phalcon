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

namespace Phalcon\Tests\Unit\Cache\Adapter\Apcu;

use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;
use Phalcon\Tests\UnitTestCase;
use stdClass;

final class GetSetTest extends UnitTestCase
{
    use ApcuTrait;

    /**
     * @return array
     */
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

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: get()/set()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @throws Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterApcuGetSet(
        mixed $value
    ): void {
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $key = uniqid();

        $result = $adapter->set($key, $value);
        $this->assertTrue($result);

        $expected = $value;
        $actual   = $adapter->get($key);
        $this->assertEquals($expected, $actual);
    }
}
