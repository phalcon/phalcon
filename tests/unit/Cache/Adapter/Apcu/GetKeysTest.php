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

final class GetKeysTest extends UnitTestCase
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: getKeys()
     *
     * @return void
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterApcuGetKeys(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $this->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $this->assertTrue($adapter->has('key-1'));
        $this->assertTrue($adapter->has('key-2'));
        $this->assertTrue($adapter->has('one-1'));
        $this->assertTrue($adapter->has('one-2'));

        $expected = [
            'ph-apcu-key-1',
            'ph-apcu-key-2',
            'ph-apcu-one-1',
            'ph-apcu-one-2',
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $this->assertEquals($expected, $actual);

        $expected = [
            'ph-apcu-one-1',
            'ph-apcu-one-2',
        ];
        $actual   = $adapter->getKeys("one");
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
}
