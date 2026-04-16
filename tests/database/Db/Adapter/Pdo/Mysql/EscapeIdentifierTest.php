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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Mysql;

use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

final class EscapeIdentifierTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo\Mysql :: escapeIdentifier()
     *
     * @dataProvider getEscapeIdentifierProvider
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2016-11-19
     * @group  mysql
     */
    public function testDbAdapterPdoMysqlEscapeIdentifier(
        string|array $identifier,
        string $expected
    ): void {
        $db = $this->container->get('db');

        $this->assertEquals(
            $expected,
            $db->escapeIdentifier($identifier)
        );
    }

    /**
     * @return array<array{0: string|array, 1: string}>
     */
    public static function getEscapeIdentifierProvider(): array
    {
        return [
            ['robots', '`robots`'],
            [['schema', 'robots'], '`schema`.`robots`'],
            ['`robots`', '```robots```'],
            [['`schema`', 'rob`ots'], '```schema```.`rob``ots`'],
        ];
    }
}
