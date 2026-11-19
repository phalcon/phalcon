<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Adapter\Pdo;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\Group;

final class ConvertBoundParamsTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: BIND_PATTERN - the pattern of
     * convertBoundParams(), as in cphalcon
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/833
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoBindPattern(): void
    {
        $this->assertSame(
            '/\?([0-9]+)|:([a-zA-Z0-9_]+):/',
            AbstractPdo::BIND_PATTERN
        );

        $db = $this->container->get('db');

        $result = $db->convertBoundParams(
            'SELECT * FROM co_invoices WHERE inv_id = :id: AND inv_title = ?1',
            ['id' => 10, 1 => 'foo']
        );

        $this->assertSame([10, 'foo'], $result['params']);
        $this->assertSame(
            'SELECT * FROM co_invoices WHERE inv_id = ? AND inv_title = ?',
            $result['sql']
        );
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: convertBoundParams()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-05-18
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoConvertBoundParams(): void
    {
        $db = $this->container->get('db');

        $result = $db->convertBoundParams(
            'SELECT * FROM co_invoices WHERE inv_id = ?0 AND inv_title = ?1',
            [10, 'foo']
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sql', $result);
        $this->assertArrayHasKey('params', $result);
        $this->assertSame([10, 'foo'], $result['params']);
        $this->assertStringContainsString('co_invoices', $result['sql']);
    }

    /**
     * Tests Phalcon\Db\Adapter\Pdo :: convertBoundParams() - the parameters
     * have the cphalcon names
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/832
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterPdoConvertBoundParamsNamedArguments(): void
    {
        $db = $this->container->get('db');

        $result = $db->convertBoundParams(
            sql: 'SELECT * FROM co_invoices WHERE inv_id = ?0',
            params: [10]
        );

        $this->assertSame([10], $result['params']);
    }
}
