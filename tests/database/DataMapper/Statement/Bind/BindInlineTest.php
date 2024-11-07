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

namespace Phalcon\Tests\Database\DataMapper\Statement\Bind;

use PDO;
use Phalcon\DataMapper\Statement\Bind;
use Phalcon\DataMapper\Statement\Select;
use Phalcon\Tests\AbstractDatabaseTestCase;
use ReflectionClass;

use function get_class;

final class BindInlineTest extends AbstractDatabaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $class = new ReflectionClass(Bind::class);
        $property = $class->getProperty('instanceCount');
        $property->setAccessible(true);
        $property->setValue(0);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: bindInline()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindBindInline(): void
    {
        $bind   = new Bind();
        $select = new Select('mysql');

        $select
            ->from('co_customers')
            ->where('inv_cst_id = ', 1)
        ;

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->bindInline("one");

        $expected = [
            "_1_1_" => ["one", 2],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->bindInline(true, PDO::PARAM_BOOL);

        $expected = [
            "_1_1_" => ["one", 2],
            "_1_2_" => [1, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $bind->bindInline(["six", "seven", 8, 9]);

        $expected = [
            "_1_1_" => ["one", 2],
            "_1_2_" => [1, 5],
            "_1_3_" => ["six", 2],
            "_1_4_" => ["seven", 2],
            "_1_5_" => [8, 1],
            "_1_6_" => [9, 1],
        ];
        $actual   = $bind->toArray();
        $this->assertEquals($expected, $actual);

        $expected = '(SELECT * FROM co_customers WHERE inv_cst_id = :_2_1_)';
        $actual   = $bind->bindInline($select);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: toArray()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindToArray()
    {
        $bind = new Bind();
        $bind->setValue('key', 'value');

        $this->assertIsArray($bind->toArray());
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: inlineArray()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindInlineArray()
    {
        $bind   = new Bind();
        $actual = $this->invokeMethod(
            $bind,
            'inlineArray',
            [
                ['value1', 'value2'],
                PDO::PARAM_STR,
            ]
        );

        $expected = '(:_1_1_, :_1_2_)';
        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Statement\Bind :: inlineValue()
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmQueryBindInlineValue()
    {
        $bind   = new Bind();

        $actual = $this->invokeMethod(
            $bind,
            'inlineValue',
            [
                'value',
                PDO::PARAM_STR,
            ]
        );

        $expected = '_1_1_';
        $this->assertSame($expected, $actual);
    }
}
