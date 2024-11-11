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

use function env;

final class InlineTest extends AbstractDatabaseTestCase
{
    /**
     * @return void
     */
    protected function setUp() : void
    {
        /**
         * This is here to ensure that the tests run fine either individually
         * or as a suite, since the static instance count will increase
         * differently depending on how the test is run (suite/on its own)
         */
        parent::setUp();

        $bind = new ReflectionClass(Bind::class);
        $property = $bind->getProperty('instanceCount');
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

        $expected = [];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $bind->inline("one");

        $expected = [
            "_1_1_" => ["one", 2],
        ];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $bind->inline(true, PDO::PARAM_BOOL);

        $expected = [
            "_1_1_" => ["one", 2],
            "_1_2_" => [true, 5],
        ];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $bind->inline(["six", "seven", 8, 9]);

        $expected = [
            "_1_1_" => ["one", 2],
            "_1_2_" => [true, 5],
            "_1_3_" => ["six", 2],
            "_1_4_" => ["seven", 2],
            "_1_5_" => [8, 1],
            "_1_6_" => [9, 1],
        ];
        $actual   = $bind->toArray();
        $this->assertSame($expected, $actual);

        $driver = env('driver');
        $select = Select::new($driver);

        $select
            ->from('co_customers')
            ->where('inv_cst_id = ', 1)
        ;

        $expected = '(SELECT * FROM co_customers WHERE inv_cst_id = :_2_1_)';
        $actual   = $bind->inline($select);
        $this->assertSame($expected, $actual);
    }
}
