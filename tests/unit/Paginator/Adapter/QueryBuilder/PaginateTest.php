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

namespace Phalcon\Tests\Unit\Paginator\Adapter\QueryBuilder;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\Repository;
use Phalcon\Storage\Exception;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Invoices;

use function is_int;

final class PaginateTest extends DatabaseTestCase
{
    use DiTrait;
    use RecordsTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        /** @var PDO $connection */
        $connection = self::getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Paginator\Adapter\QueryBuilder :: paginate()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group common
     */
    public function testPaginatorAdapterQuerybuilderPaginate(): void
    {
        /**
         * Sqlite does not like `where` that much and locks the database
         */
        /** @var PDO $connection */
        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === self::getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');

        $manager = $this->getService('modelsManager');
        $builder = $manager
            ->createBuilder()
            ->from(Invoices::class)
        ;

        $paginator = new QueryBuilder(
            [
                'builder' => $builder,
                'limit'   => 5,
                'page'    => 1,
            ]
        );

        $page = $paginator->paginate();

        $this->assertInstanceOf(Repository::class, $page);
        $this->assertCount(5, $page->getItems());
        $this->assertEquals(1, $page->getPrevious());
        $this->assertEquals(2, $page->getNext());
        $this->assertEquals(4, $page->getLast());
        $this->assertEquals(5, $page->getLimit());
        $this->assertEquals(1, $page->getCurrent());
        $this->assertEquals(5, $page->limit);
        $this->assertEquals(17, $page->getTotalItems());
        $this->assertTrue(is_int($page->getTotalItems()));
    }

    /**
     * Tests Phalcon\Paginator\Adapter\QueryBuilder :: paginate() - groupBy
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-01-29
     *
     * @group  mysql
     * @group  pgsql
     */
    public function testPaginatorAdapterQuerybuilderPaginateGroupBy(): void
    {
        /**
         * Sqlite does not like `where` that much and locks the database
         */

        /** @var PDO $connection */
        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === self::getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');

        $manager = $this->getService('modelsManager');
        $builder = $manager
            ->createBuilder()
            ->from(Invoices::class)
        ;

        $paginator = new QueryBuilder(
            [
                'builder' => $builder,
                'limit'   => 5,
                'page'    => 1,
            ]
        );

        $page = $paginator->paginate();

        $this->assertInstanceOf(Repository::class, $page);
        $this->assertCount(5, $page->getItems());
        $this->assertEquals(1, $page->getPrevious());
        $this->assertEquals(2, $page->getNext());
        $this->assertEquals(6, $page->getLast());
        $this->assertEquals(5, $page->getLimit());
        $this->assertEquals(1, $page->getCurrent());
        $this->assertEquals(5, $page->limit);
        $this->assertEquals(28, $page->getTotalItems());
        $this->assertTrue(is_int($page->getTotalItems()));

        $builder = $manager
            ->createBuilder()
            ->from(Invoices::class)
            ->where('inv_cst_id = :custId:', ['custId' => 2])
        ;

        $paginator->setQueryBuilder($builder);

        $page = $paginator->paginate();

        $this->assertInstanceOf(Repository::class, $page);
        $this->assertCount(5, $page->getItems());
        $this->assertEquals(1, $page->getPrevious());
        $this->assertEquals(2, $page->getNext());
        $this->assertEquals(4, $page->getLast());
        $this->assertEquals(5, $page->getLimit());
        $this->assertEquals(1, $page->getCurrent());
        $this->assertEquals(5, $page->limit);
        $this->assertEquals(17, $page->getTotalItems());
        $this->assertTrue(is_int($page->getTotalItems()));
    }

    /**
     * Tests Phalcon\Paginator\Adapter\QueryBuilder :: paginate()
     *
     * @issue  14639
     *
     * @group  mysql
     *
     * @throws Exception
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-15
     *
     */
    public function testPaginatorAdapterQuerybuilderPaginateView(): void
    {
        $this->setDiService('view');

        /** @var PDO $connection */
        $connection = self::getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === self::getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $criteria = Criteria::fromInput(
            $this->container,
            Invoices::class,
            []
        );
        $builder  = $criteria->createBuilder();

        $paginator = new QueryBuilder(
            [
                'builder' => $builder,
                'limit'   => 5,
                'page'    => 1,
            ]
        );

        $page = $paginator->paginate();
        $this->assertCount(5, $page->getItems());

        $view = $this->getService('view');
        $view->setVar('page', $page);

        $actual = $view->getVar('page');
        $this->assertInstanceOf(Repository::class, $actual);


        $view = $this->getService('view');
        $view->setVar('paginate', $paginator->paginate());

        $actual = $view->getVar('paginate');
        $this->assertInstanceOf(Repository::class, $actual);
    }
}
