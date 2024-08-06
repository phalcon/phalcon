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

namespace Phalcon\Tests\Unit\Db\Column;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Db\Column;

final class ConstantsTest extends DatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Column :: constants
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-10-26
     *
     * @group  common
     */
    public function checkClassConstants(): void
    {
        $bind = [
            [Column::BIND_PARAM_BLOB, 3],
            [Column::BIND_PARAM_BOOL, 5],
            [Column::BIND_PARAM_DECIMAL, 32],
            [Column::BIND_PARAM_INT, 1],
            [Column::BIND_PARAM_NULL, 0],
            [Column::BIND_PARAM_STR, 2],
            [Column::BIND_SKIP, 1024],
        ];
        foreach ($bind as $item) {
            $this->assertEquals($item[1], $item[0]);
        }

        $type = [
            [Column::TYPE_BIGINTEGER, 14],
            [Column::TYPE_BIT, 19],
            [Column::TYPE_BINARY, 26],
            [Column::TYPE_BLOB, 11],
            [Column::TYPE_BOOLEAN, 8],
            [Column::TYPE_CHAR, 5],
            [Column::TYPE_DATE, 1],
            [Column::TYPE_DATETIME, 4],
            [Column::TYPE_DECIMAL, 3],
            [Column::TYPE_DOUBLE, 9],
            [Column::TYPE_ENUM, 18],
            [Column::TYPE_FLOAT, 7],
            [Column::TYPE_INTEGER, 0],
            [Column::TYPE_JSON, 15],
            [Column::TYPE_JSONB, 16],
            [Column::TYPE_LONGBLOB, 13],
            [Column::TYPE_LONGTEXT, 24],
            [Column::TYPE_MEDIUMBLOB, 12],
            [Column::TYPE_MEDIUMINTEGER, 21],
            [Column::TYPE_MEDIUMTEXT, 23],
            [Column::TYPE_SMALLINTEGER, 22],
            [Column::TYPE_TEXT, 6],
            [Column::TYPE_TIME, 20],
            [Column::TYPE_TIMESTAMP, 17],
            [Column::TYPE_TINYBLOB, 10],
            [Column::TYPE_TINYINTEGER, 26],
            [Column::TYPE_TINYTEXT, 25],
            [Column::TYPE_VARBINARY, 27],
            [Column::TYPE_VARCHAR, 2],
        ];
        foreach ($type as $item) {
            $this->assertEquals($item[1], $item[0]);
        }
    }
}
