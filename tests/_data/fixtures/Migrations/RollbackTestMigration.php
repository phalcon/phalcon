<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\Migrations;

class RollbackTestMigration extends AbstractMigration
{
    protected $table = 'co_rb_test_model';

    protected function getSqlMysql(): array
    {
        return [
            'drop table if exists co_rb_test_model;',
            "
create table co_rb_test_model (
    id   smallint,
    name varchar(10) not null
);",
        ];
    }

    protected function getSqlSqlite(): array
    {
        return [];
    }

    protected function getSqlPgsql(): array
    {
        return [
            'DROP TABLE IF EXISTS co_rb_test_model;',
            "
create table co_rb_test_model (
    id   smallint,
    name varchar(10) not null
);",
        ];
    }

    protected function getSqlSqlsrv(): array
    {
        return [];
    }
}
