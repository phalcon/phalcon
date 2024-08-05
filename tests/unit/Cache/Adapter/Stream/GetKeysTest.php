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

namespace Phalcon\Tests\Unit\Cache\Adapter\Stream;

use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\UnitTestCase;

use function outputDir;
use function sort;
use function uniqid;

final class GetKeysTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $this->assertTrue($adapter->clear());

        $key1 = uniqid('key');
        $key2 = uniqid('key');
        $key3 = uniqid('one');
        $key4 = uniqid('one');

        $result = $adapter->set($key1, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key2, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key3, 'test');
        $this->assertNotFalse($result);
        $result = $adapter->set($key4, 'test');
        $this->assertNotFalse($result);

        $this->assertTrue($adapter->has($key1));
        $this->assertTrue($adapter->has($key2));
        $this->assertTrue($adapter->has($key3));
        $this->assertTrue($adapter->has($key4));

        $expected = [
            'ph-strm' . $key1,
            'ph-strm' . $key2,
            'ph-strm' . $key3,
            'ph-strm' . $key4,
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'ph-strm' . $key3,
            'ph-strm' . $key4,
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertEquals($expected, $actual);

        $this->safeDeleteDirectory(outputDir('ph-strm'));
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function testCacheAdapterStreamGetKeysIssue14190(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'basePrefix-',
            ]
        );

        $adapter->clear();

        $actual = $adapter->set('key', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $this->assertNotFalse($actual);

        $expected = [
            'basePrefix-key',
            'basePrefix-key1',
        ];

        $actual = $adapter->getKeys();
        sort($actual);

        $this->assertEquals($expected, $actual);

        foreach ($expected as $key) {
            $actual = $adapter->delete($key);
            $this->assertTrue($actual);
        }

        $this->safeDeleteDirectory(outputDir('basePrefix-'));
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: getKeys()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author ekmst <https://github.com/ekmst>
     * @since  2020-09-09
     * @issue  cphalcon/#14190
     */
    public function testCacheAdapterStreamGetKeysPrefix(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'pref-',
            ]
        );

        $actual = $adapter->clear();
        $this->assertTrue($actual);
        $actual = $adapter->getKeys();
        $this->assertEmpty($actual);

        $actual = $adapter->set('key', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('key1', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('somekey', 'test');
        $this->assertNotFalse($actual);
        $actual = $adapter->set('somekey1', 'test');
        $this->assertNotFalse($actual);

        $expected = [
            'pref-key',
            'pref-key1',
            'pref-somekey',
            'pref-somekey1',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'pref-somekey',
            'pref-somekey1',
        ];

        $actual = $adapter->getKeys('so');
        sort($actual);
        $this->assertEquals($expected, $actual);

        $actual = $adapter->clear();
        $this->assertTrue($actual);

        $this->safeDeleteDirectory(outputDir('pref-'));
    }
}
