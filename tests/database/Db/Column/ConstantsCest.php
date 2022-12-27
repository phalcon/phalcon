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

namespace Phalcon\Tests\Database\Db\Column;

use DatabaseTester;
use Phalcon\Db\Column;

class ConstantsCest
{
    /**
     * Tests Phalcon\Db\Column :: constants
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-10-26
     *
     * @group  common
     */
    public function checkClassConstants(DatabaseTester $I)
    {
        $I->wantToTest("Db\Column :: constants");

        $I->assertSame(3, Column::BIND_PARAM_BLOB);
        $I->assertSame(5, Column::BIND_PARAM_BOOL);
        $I->assertSame(32, Column::BIND_PARAM_DECIMAL);
        $I->assertSame(1, Column::BIND_PARAM_INT);
        $I->assertSame(0, Column::BIND_PARAM_NULL);
        $I->assertSame(2, Column::BIND_PARAM_STR);
        $I->assertSame(1024, Column::BIND_SKIP);

        $I->assertSame(14, Column::TYPE_BIGINTEGER);
        $I->assertSame(19, Column::TYPE_BIT);
        $I->assertSame(11, Column::TYPE_BLOB);
        $I->assertSame(8, Column::TYPE_BOOLEAN);
        $I->assertSame(5, Column::TYPE_CHAR);
        $I->assertSame(1, Column::TYPE_DATE);
        $I->assertSame(4, Column::TYPE_DATETIME);
        $I->assertSame(3, Column::TYPE_DECIMAL);
        $I->assertSame(9, Column::TYPE_DOUBLE);
        $I->assertSame(18, Column::TYPE_ENUM);
        $I->assertSame(7, Column::TYPE_FLOAT);
        $I->assertSame(0, Column::TYPE_INTEGER);
        $I->assertSame(15, Column::TYPE_JSON);
        $I->assertSame(16, Column::TYPE_JSONB);
        $I->assertSame(13, Column::TYPE_LONGBLOB);
        $I->assertSame(24, Column::TYPE_LONGTEXT);
        $I->assertSame(12, Column::TYPE_MEDIUMBLOB);
        $I->assertSame(21, Column::TYPE_MEDIUMINTEGER);
        $I->assertSame(23, Column::TYPE_MEDIUMTEXT);
        $I->assertSame(22, Column::TYPE_SMALLINTEGER);
        $I->assertSame(6, Column::TYPE_TEXT);
        $I->assertSame(20, Column::TYPE_TIME);
        $I->assertSame(17, Column::TYPE_TIMESTAMP);
        $I->assertSame(10, Column::TYPE_TINYBLOB);
        $I->assertSame(26, Column::TYPE_TINYINTEGER);
        $I->assertSame(25, Column::TYPE_TINYTEXT);
        $I->assertSame(2, Column::TYPE_VARCHAR);
    }
}
