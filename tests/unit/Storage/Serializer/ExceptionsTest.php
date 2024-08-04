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

use Exception;
use InvalidArgumentException;
use Phalcon\Storage\Serializer\Base64;
use Phalcon\Storage\Serializer\Igbinary;
use Phalcon\Storage\Serializer\Json;
use Phalcon\Storage\Serializer\Msgpack;
use Phalcon\Storage\Serializer\Php;
use Phalcon\Support\Collection;
use Phalcon\Tests\Fixtures\Storage\Serializer\Base64DecodeFixture;
use Phalcon\Tests\Fixtures\Storage\Serializer\IgbinarySerializeFixture;
use Phalcon\Tests\Fixtures\Storage\Serializer\IgbinaryUnserializeFixture;
use Phalcon\Tests\UnitTestCase;
use stdClass;

use function json_encode;
use function trigger_error;

use const E_WARNING;

final class ExceptionsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Storage\Serializer\Base64 :: serialize() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerBase64SerializeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Data for the serializer must of type string'
        );

        $serializer = new Base64(1234);
        $serializer->serialize();
    }

    /**
     * Tests Phalcon\Storage\Serializer\Base64 :: unserialize() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerBase64UnserializeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Data for the unserializer must of type string'
        );

        $serializer = new Base64();
        $serializer->unserialize(1234);
    }

    /**
     * Tests Phalcon\Storage\Serializer\Base64 :: unserialize() - fail empty
     * string
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-02-24
     */
    public function testStorageSerializerBase64UnserializeFailReturnsEmptyString(): void
    {
        $serializer = new Base64DecodeFixture();

        $serializer->unserialize("Phalcon Framework");
        $actual = $serializer->getData();
        $this->assertEmpty($actual);

        $actual = $serializer->isSuccess();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Storage\Serializer\Igbinary :: serialize() - fail empty
     * string
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-02-24
     */
    public function testStorageSerializerIgbinarySerializeFailReturnsEmptyString(): void
    {
        $serializer = new IgbinarySerializeFixture();

        $serializer->setData('Phalcon Framework');
        $actual = $serializer->serialize();
        $this->assertEmpty($actual);

        $actual = $serializer->isSuccess();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Storage\Serializer\Igbinary :: unserialize() - fail empty
     * string
     *
     * @return void
     *
     * @throws Exception
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-02-24
     */
    public function testStorageSerializerIgbinaryUnserializeFailReturnsEmptyString(): void
    {
        $serializer = new IgbinaryUnserializeFixture();

        $serializer->unserialize("Phalcon Framework");
        $actual = $serializer->getData();
        $this->assertEmpty($actual);

        $actual = $serializer->isSuccess();
        $this->assertFalse($actual);
    }

    /**
     * Tests Phalcon\Storage\Serializer\Json :: serialize() - error
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerJsonSerializeError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Data for the JSON serializer cannot be of type 'object' " .
            "without implementing 'JsonSerializable'"
        );

        $example      = new stdClass();
        $example->one = 'two';

        $serializer = new Json($example);
        $serializer->serialize();
    }

    /**
     * Tests Phalcon\Storage\Serializer\Json :: serialize() - object
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerJsonSerializeObject(): void
    {
        $collection1 = new Collection();
        $collection1->set('one', 'two');
        $collection2 = new Collection();
        $collection2->set('three', 'four');
        $collection2->set('object', $collection1);

        $serializer = new Json($collection2);

        $data     = [
            'three'  => 'four',
            'object' => [
                'one' => 'two',
            ],
        ];
        $expected = json_encode($data);
        $actual   = $serializer->serialize();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Serializer\Msgpack :: unserialize() - error
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerMsgpackUnserializeError(): void
    {
        $serializer = new Msgpack();

        $serialized = '??hello?messagepack"';
        $serializer->unserialize($serialized);

        $this->assertEmpty($serializer->getData());
    }

    /**
     * Tests Phalcon\Storage\Serializer\Php :: unserialize() - error
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerPhpUnserializeError(): void
    {
        $serializer = new Php();

        $serialized = '{??hello?unserialize"';
        $serializer->unserialize($serialized);

        $this->assertEmpty($serializer->getData());
    }

    /**
     * Tests Phalcon\Storage\Serializer\Php :: unserialize() - error not string
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testStorageSerializerPhpUnserializeErrorNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Data for the unserializer must of type string'
        );

        $serializer = new Php();

        $serialized = new stdClass();
        $serializer->unserialize($serialized);
    }
}
