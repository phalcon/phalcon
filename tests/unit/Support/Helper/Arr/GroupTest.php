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

namespace Phalcon\Tests\Unit\Support\Helper\Arr;

use Phalcon\Support\Helper\Arr\Group;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

final class GroupTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Arr :: group()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrGroup(): void
    {
        $object     = new Group();
        $collection = [
            [
                'name' => 'Paul',
                'age'  => 34,
            ],
            [
                'name' => 'Peter',
                'age'  => 31,
            ],
            [
                'name' => 'John',
                'age'  => 29,
            ],
        ];

        $expected = [
            34 => [
                [
                    'name' => 'Paul',
                    'age'  => 34,
                ],
            ],
            31 => [
                [
                    'name' => 'Peter',
                    'age'  => 31,
                ],
            ],
            29 => [
                [
                    'name' => 'John',
                    'age'  => 29,
                ],
            ],
        ];

        $actual = $object($collection, 'age');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Arr :: group() - function
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrGroupFunction(): void
    {
        $object     = new Group();
        $collection = ['one', 'two', 'three'];

        $expected = [
            3 => ['one', 'two'],
            5 => ['three'],
        ];
        $actual   = $object($collection, 'strlen');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Arr :: group() - object
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    #[Test]
    public function testSupportHelperArrGroupObject(): void
    {
        $object      = new Group();
        $peter       = new stdClass();
        $peter->name = 'Peter';
        $peter->age  = 34;

        $paul       = new stdClass();
        $paul->name = 'Paul';
        $paul->age  = 31;

        $collection = [
            'peter' => $peter,
            'paul'  => $paul,
        ];


        $expected = [
            'Peter' => [$peter],
            'Paul'  => [$paul],
        ];
        $actual   = $object($collection, 'name');
        $this->assertSame($expected, $actual);
    }
}
