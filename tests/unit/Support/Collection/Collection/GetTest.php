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

namespace Phalcon\Tests\Unit\Support\Collection\Collection;

use Phalcon\Support\Collection;
use Phalcon\Tests\UnitTestCase;
use stdClass;

final class GetTest extends UnitTestCase
{
    /**
     * @return array[]
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

    /**
     * Tests Phalcon\Support\Collection :: get()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-12-01
     * @issue  https://github.com/phalcon/cphalcon/issues/15370
     */
    public function testSupportCollectionGet(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
            'seven' => '',
            'eight' => null,
        ];

        $collection = new Collection($data);
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

        $expected = 'two';
        $actual   = $collection->get('one', 'fallback');
        $this->assertSame($expected, $actual);

        $expected = '';
        $actual   = $collection->get('seven', 'fallback');
        $this->assertSame($expected, $actual);

        $expected = 'fallback';
        $actual   = $collection->get('eight', 'fallback');
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
    public function testSupportCollectionGetCast(
        string $cast,
        mixed $value,
        mixed $expected
    ): void {
        $collection = new Collection(
            [
                'value' => $value,
            ]
        );

        $actual = $collection->get('value', null, $cast);
        $this->assertEquals($expected, $actual);
    }
}
