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

namespace Phalcon\Tests\Integration\Storage\Adapter\Stream;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use stdClass;
use UnitTester;

use function file_put_contents;
use function is_dir;
use function mkdir;
use function outputDir;
use function sleep;

class GetSetCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: set()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamSet(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - set()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $data   = 'Phalcon Framework';
        $actual = $adapter->set('test-key', $data);
        $I->assertTrue($actual);

        $target = outputDir() . 'ph-strm/te/st/-k/';
        $I->amInPath($target);
        $I->openFile('test-key');
        $expected = 's:3:"ttl";i:3600;s:7:"content";s:25:"s:17:"Phalcon Framework";";}';

        $I->seeInThisFile($expected);
        $I->safeDeleteFile($target . 'test-key');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: get()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGet(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - get()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $target = outputDir() . 'ph-strm/te/st/-k/';
        $data   = 'Phalcon Framework';
        $actual = $adapter->set('test-key', $data);
        $I->assertTrue($actual);

        $expected = 'Phalcon Framework';
        $actual   = $adapter->get('test-key');
        $I->assertNotNull($actual);
        $I->assertEquals($expected, $actual);

        $expected        = new stdClass();
        $expected->one   = 'two';
        $expected->three = 'four';

        $actual = $adapter->set('test-key', $expected);
        $I->assertTrue($actual);

        $actual = $adapter->get('test-key');
        $I->assertEquals($expected, $actual);

        $I->safeDeleteFile($target . 'test-key');
    }

    /**
     * Tests Phalcon\Storage\Adapter\Stream :: get() - errors
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetErrors(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - get() - errors');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $helper,
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
        $actual   = $adapter->get('unknown', 'test');
        $I->assertEquals($expected, $actual);

        // Invalid stored object
        $actual = file_put_contents(
            $target . 'test-key',
            '{'
        );
        $I->assertNotFalse($actual);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $I->assertEquals($expected, $actual);

        // Expiry
        $data = 'Phalcon Framework';

        $actual = $adapter->set('test-key', $data, 1);
        $I->assertTrue($actual);

        sleep(2);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $I->assertEquals($expected, $actual);

        $I->safeDeleteFile($target . 'test-key');
    }
}
