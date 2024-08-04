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

namespace Phalcon\Tests\Unit\Storage\Serializer;

use Phalcon\Storage\Serializer\Base64;
use Phalcon\Storage\Serializer\Igbinary;
use Phalcon\Storage\Serializer\Json;
use Phalcon\Storage\Serializer\Msgpack;
use Phalcon\Storage\Serializer\None;
use Phalcon\Storage\Serializer\Php;
use Phalcon\Tests\UnitTestCase;

final class GetSetDataTest extends UnitTestCase
{
    /**
     * @return array
     */
    public static function getExamples(): array
    {
        return [
            [
                Base64::class,
            ],
            [
                Igbinary::class,
            ],
            [
                Json::class,
            ],
            [
                Msgpack::class,
            ],
            [
                None::class,
            ],
            [
                Php::class,
            ],
        ];
    }

    /**
     * Tests Phalcon\Storage\Serializer\ :: getData()/setData()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-02-24
     */
    public function testStorageSerializerGetSetData(
        string $class
    ) {
        $data       = ['Phalcon Framework'];
        $serializer = new $class();

        $actual = $serializer->getData();
        $this->assertNull($actual);

        $serializer->setData($data);

        $expected = $data;
        $actual   = $serializer->getData();
        $this->assertSame($expected, $actual);
    }
}
