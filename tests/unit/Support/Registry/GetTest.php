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

namespace Phalcon\Tests\Unit\Support\Registry;

use Codeception\Example;
use Phalcon\Support\Registry;
use stdClass;
use Phalcon\Tests\UnitTestCase;

final class GetTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Registry :: get()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSupportRegistryGet(): void
    {
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $registry = new Registry($data);

        $expected = 'four';
        $actual   = $registry->get('three');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Registry :: get() - cast
     *
     * @dataProvider getExamples
     *
     * @since        2019-10-12
     */
    public function testSupportRegistryGetCast(
        string $cast,
        mixed $value,
        mixed $expected
    ): void {
        $collection = new Registry(
            [
                'value' => $value,
            ]
        );

        $actual = $collection->get('value', null, $cast);
        $this->assertEquals($expected, $actual);
    }

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
}
