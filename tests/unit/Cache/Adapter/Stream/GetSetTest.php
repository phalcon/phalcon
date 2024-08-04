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

use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Exception as CacheException;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use stdClass;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function outputDir;
use function sleep;

final class GetSetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream :: set()
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamSet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $data   = 'Phalcon Framework';
        $actual = $adapter->set('test-key', $data);
        $this->assertTrue($actual);

        $target = outputDir() . 'ph-strm/te/st/-k/';

        $data = 's:3:"ttl";i:3600;s:7:"content";s:25:"s:17:"Phalcon Framework";';
        $this->assertFileContentsContains($target . 'test-key', $data);

        $this->safeDeleteFile($target . 'test-key');
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: get()
     *
     * @return void
     *
     * @return void
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    /**
     */
    public function testCacheAdapterStreamGet(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $target = outputDir() . 'ph-strm/te/st/-k/';
        $data   = 'Phalcon Framework';
        $actual = $adapter->set('test-key', $data);
        $this->assertTrue($actual);

        $expected = 'Phalcon Framework';
        $actual   = $adapter->get('test-key');
        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);

        $expected        = new stdClass();
        $expected->one   = 'two';
        $expected->three = 'four';

        $actual = $adapter->set('test-key', $expected);
        $this->assertTrue($actual);

        $actual = $adapter->get('test-key');
        $this->assertEquals($expected, $actual);

        $this->safeDeleteFile($target . 'test-key');
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: get() - with prefix
     *
     * @return void
     *
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-06-01
     * @issue  16348
     */
    public function testCacheAdapterStreamGetWithPrefix(): void
    {
        $this->safeDeleteDirectory(outputDir() . 'en/');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'en',
            ]
        );

        $target = outputDir() . 'en/';

        $data   = 123;
        $actual = $adapter->set('men', $data);
        $this->assertTrue($actual);

        $expected = 123;
        $actual   = $adapter->get('men');
        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);


        $data   = 'abc';
        $actual = $adapter->set('barmen', $data);
        $this->assertTrue($actual);

        $expected = 'abc';
        $actual   = $adapter->get('barmen');
        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);

        $data   = 'xyz';
        $actual = $adapter->set('bar', $data);
        $this->assertTrue($actual);

        $expected = 'xyz';
        $actual   = $adapter->get('bar');
        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'enbar',
            'enbarmen',
            'enmen',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $this->safeDeleteFile($target);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: get() - errors
     *
     * @return void
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamGetErrors(): void
    {
        if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
            $this->markTestSkipped('Since PHP 8.3 warnings causing session ID/Name lock.');
        }

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $target = outputDir() . 'ph-strm/te/st/-k/';
        if (true !== is_dir($target)) {
            mkdir($target, 0777, true);
        }

        // Unknown key
        $expected = 'test';
        $actual   = $adapter->get(uniqid(), 'test');
        $this->assertEquals($expected, $actual);

        // Invalid stored object
        $actual = file_put_contents(
            $target . 'test-key',
            '{'
        );
        $this->assertNotFalse($actual);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $this->assertEquals($expected, $actual);

        // Expiry
        $data = 'Phalcon Framework';

        $actual = $adapter->set('test-key', $data, 1);
        $this->assertTrue($actual);

        sleep(2);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $this->assertEquals($expected, $actual);

        $this->safeDeleteFile($target . 'test-key');
    }
}
