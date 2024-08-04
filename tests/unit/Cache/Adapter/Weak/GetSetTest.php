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

namespace Phalcon\Tests\Unit\Cache\Adapter\Weak;

use ArrayObject;
use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use SplObjectStorage;
use SplQueue;
use stdClass;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\Weak :: get()
     *
     * @dataProvider getExamples
     *
     * @return void
     * @throws HelperException
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterWeakGetSet(
        mixed $value
    ): void {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $key = uniqid();

        $result = $adapter->set($key, $value);
        $this->assertTrue($result);

        $expected = $value;
        $actual   = $adapter->get($key);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            [
                new stdClass(),
            ],
            [
                new ArrayObject(),
            ],
            [
                new SplObjectStorage(),
            ],
            [
                new SplQueue(),
            ],
        ];
    }
}
