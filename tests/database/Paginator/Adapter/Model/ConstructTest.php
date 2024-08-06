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

namespace Phalcon\Tests\Database\Paginator\Adapter\Model;

use Phalcon\Paginator\Adapter\Model;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;
use PDO;

use function uniqid;

final class ConstructTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        /** @var PDO $connection */
        $connection = self::getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Paginator\Adapter\Model :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-11-01
     *
     * @group common
     */
    public function testPaginatorAdapterModelConstruct(): void
    {
        $title = uniqid('inv-');
        /** @var PDO $connection */
        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
        $migration->insert(4, null, 0, $title);

        $paginator = new Model(
            [
                'model' => Invoices::class,
                'limit' => 10,
                'page'  => 1,
            ]
        );

        $this->assertInstanceOf(Model::class, $paginator);
    }
}
