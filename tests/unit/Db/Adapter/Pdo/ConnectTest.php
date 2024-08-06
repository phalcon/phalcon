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

namespace Phalcon\Tests\Unit\Db\Adapter\Pdo;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

use function getOptionsMysql;

final class ConnectTest extends DatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: connect() - persistent
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-20
     *
     * @group  mysql
     */
    public function dbAdapterPdoConnectPersistent(): void
    {
        $options               = getOptionsMysql();
        $options['persistent'] = true;
        $options['options']    = [
            PDO::ATTR_EMULATE_PREPARES  => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        $connection = (new PdoFactory())->newInstance('mysql', $options);

        $expected = $options;
        $actual   = $connection->getDescriptor();

        $this->assertEquals($expected, $actual);
    }
}
