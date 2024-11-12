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

namespace Phalcon\Tests\Database\DataMapper\Statement;

use PDO;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Statement\Bind;
use Phalcon\Tests\AbstractUnitTestCase;
use ReflectionClass;
use ReflectionException;

use function array_filter;
use function date;
use function env;
use function explode;
use function file_exists;
use function file_get_contents;
use function get_class;
use function getOptionsMysql;
use function getOptionsPostgresql;
use function getOptionsSqlite;
use function implode;
use function is_string;
use function preg_match;
use function preg_split;
use function sprintf;
use function strlen;
use function substr;
use function trim;

use const PREG_SPLIT_NO_EMPTY;

abstract class AbstractStatementTestCase extends AbstractUnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
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
}
