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

namespace Phalcon\Tests\Unit\Support\Collection\ReadOnlyCollection;

use Codeception\Example;
use Phalcon\Support\Collection\ReadOnlyCollection;
use stdClass;
use Phalcon\Tests\UnitTestCase;

final class GetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Collection\ReadOnlyCollection :: get()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportCollectionGet(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new ReadOnlyCollection($data);
        $expected   = 'four';

        $actual = $collection->get('three');
        $this->assertSame($expected, $actual);

        $actual = $collection->get('THREE');
        $this->assertSame($expected, $actual);

        $actual = $collection->get(uniqid(), 'four');
        $this->assertSame($expected, $actual);

        $actual = $collection['three'];
        $this->assertSame($expected, $actual);

        $actual = $collection->three;
        $this->assertSame($expected, $actual);

        $actual = $collection->offsetGet('three');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Collection :: get() - cast
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function helperArrGetCast(
        string $cast,
        mixed $value,
        mixed $expected
    ): void {
        $collection = new ReadOnlyCollection(
            [
                'value' => $value,
            ]
        );

        $actual   = $collection->get('value', null, $cast);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getExamples(): array
    {
        $sample      = new stdClass();
        $sample->one = 'two';

        return [
            [
                'boolean',
                1,
                true,
            ],
            [
                'bool',
                1,
                true,
            ],
            [
                'integer',
                "123",
                123,
            ],
            [
                'int',
                "123",
                123,
            ],
            [
                'float',
                "123.45",
                123.45,
            ],
            [
                'double',
                "123.45",
                123.45,
            ],
            [
                'string',
                123,
                "123",
            ],
            [
                'array',
                $sample,
                ['one' => 'two'],
            ],
            [
                'object',
                ['one' => 'two'],
                $sample,
            ],
            [
                'null',
                1234,
                null,
            ],
        ];
    }
}
