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

namespace Phalcon\Tests\Unit\Cache\Adapter\Libmemcached;

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Storage\Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\LibmemcachedTrait;
use stdClass;

use function getOptionsLibmemcached;

final class GetSetTest extends UnitTestCase
{
    use LibmemcachedTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: get()/set()
     *
     * @dataProvider getExamples
     *
     * @param Example $example
     *
     * @return void
     * @throws HelperException
     * @throws Exception
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterLibmemcachedGetSet(
        mixed $value
    ): void {
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $serializer,
            getOptionsLibmemcached()
        );

        $key    = uniqid();
        $actual = $adapter->set($key, $value);
        $this->assertTrue($actual);

        $expected = $value;
        $actual   = $adapter->get($key);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Libmemcached :: get()/set() - custom
     * serializer
     *
     * @return void
     *
     * @throws Exception
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterLibmemcachedGetSetCustomSerializer(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Libmemcached(
            $serializer,
            array_merge(
                getOptionsLibmemcached(),
                [
                    'defaultSerializer' => 'Base64',
                ]
            )
        );

        $key    = uniqid();
        $source = 'Phalcon Framework';
        $actual = $adapter->set($key, $source);
        $this->assertTrue($actual);

        $expected = $source;
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
                false,
            ],
            [
                null,
            ],
            [
                new stdClass(),
            ],
        ];
    }
}
