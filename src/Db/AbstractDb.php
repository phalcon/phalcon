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

namespace Phalcon\Db;

use function ini_set;

/**
 * Phalcon\Db and its related classes provide a simple SQL database interface
 * for Phalcon Framework. The Phalcon\Db is the basic class you use to connect
 * your PHP application to an RDBMS. There is a different adapter class for each
 * brand of RDBMS.
 *
 * This component is intended to lower level database operations. If you want to
 * interact with databases using higher level of abstraction use
 * Phalcon\Mvc\Model.
 *
 * Phalcon\Db\AbstractDb is an abstract class. You only can use it with a
 * database adapter like Phalcon\Db\Adapter\Pdo
 *
 *```php
 * use Phalcon\Db;
 * use Phalcon\Db\Exception;
 * use Phalcon\Db\Adapter\Pdo\Mysql as MysqlConnection;
 *
 * try {
 *     $connection = new MysqlConnection(
 *         [
 *             "host"     => "192.168.0.11",
 *             "username" => "sigma",
 *             "password" => "secret",
 *             "dbname"   => "blog",
 *             "port"     => "3306",
 *         ]
 *     );
 *
 *     $result = $connection->query(
 *         "SELECT * FROM robots LIMIT 5"
 *     );
 *
 *     $result->setFetchMode(Enum::FETCH_NUM);
 *
 *     while ($robot = $result->fetch()) {
 *         print_r($robot);
 *     }
 * } catch (Exception $e) {
 *     echo $e->getMessage(), PHP_EOL;
 * }
 * ```
 */
abstract class AbstractDb
{
    /**
     * Enables/disables options in the Database component
     */
    public static function setup(array $options): void
    {
        /**
         * Enables/Disables globally the escaping of SQL identifiers
         */
        if (true === isset($options["escapeSqlIdentifiers"])) {
            ini_set(
                "phalcon.db.escape_identifiers",
                $options["escapeSqlIdentifiers"]
            );
        }

        /**
         * Force cast bound values in the PHP userland
         */
        if (true === isset($options["forceCasting"])) {
            ini_set("phalcon.db.force_casting", $options["forceCasting"]);
        }
    }
}
