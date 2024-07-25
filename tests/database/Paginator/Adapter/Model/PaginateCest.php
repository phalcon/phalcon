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

use DatabaseTester;
use PDO;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Repository;
use Phalcon\Storage\Exception;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Invoices;

class PaginateCest
{
    use DiTrait;
    use RecordsTrait;

    public function _before(DatabaseTester $I)
    {
        $this->setNewFactoryDefault();
        $this->setDatabase($I);

        /** @var PDO $connection */
        $connection = $I->getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * @param DatabaseTester $I
     *
     * @group mysql
     * @group sqlite
     * @group pgsql
     */
    public function paginatorAdapterModelPaginate(DatabaseTester $I)
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate()');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === $I->getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $paginator = new Model(
            [
                'model' => Invoices::class,
                'limit' => 5,
                'page'  => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(5, $page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(2, $page->getNext());
        $I->assertEquals(15, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());

        // Middle Page
        $paginator->setCurrentPage(3);
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);
        $I->assertCount(5, $page->getItems());
        $I->assertEquals(2, $page->getPrevious());
        $I->assertEquals(4, $page->getNext());
        $I->assertEquals(15, $page->getLast());
        $I->assertEquals(3, $page->getCurrent());

        // Last Page
        $paginator->setCurrentPage(5);
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);
        $I->assertCount(5, $page->getItems());
        $I->assertEquals(4, $page->getPrevious());
        $I->assertEquals(6, $page->getNext());
        $I->assertEquals(15, $page->getLast());
        $I->assertEquals(5, $page->getCurrent());
    }

    /**
     * @param DatabaseTester $I
     *
     * @group mysql
     * @group sqlite
     */
    public function paginatorAdapterModelPaginateBind(DatabaseTester $I)
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - bind');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);

        $invId = ('sqlite' === $I->getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $parameters = [
            'columns'    => 'inv_cst_id',
            'conditions' => 'inv_cst_id >= :d1:',
            'bind'       => [
                'd1' => '2',
            ],
            'order'      => 'inv_cst_id',
            'limit'      => '11',   // will be ignored
        ];

        $paginator = new Model(
            [
                'model'      => Invoices::class,
                'parameters' => $parameters,
                'limit'      => 5,
                'page'       => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(5, $page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(2, $page->getNext());
        $I->assertEquals(9, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());
    }

    /**
     * @param DatabaseTester $I
     *
     * @group mysql
     * @group sqlite
     * @group pgsql
     */
    public function paginatorAdapterModelPaginateParametersString(DatabaseTester $I): void
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - parameters string');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === $I->getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $paginator = new Model(
            [
                'model'      => Invoices::class,
                'parameters' => 'inv_cst_id >= 2',
                'limit'      => 5,
                'page'       => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(5, $page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(2, $page->getNext());
        $I->assertEquals(9, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());
    }

    /**
     * @param DatabaseTester $I
     *
     * @group mysql
     * @group sqlite
     * @group pgsql
     */
    public function paginatorAdapterModelPaginateParametersArrayString(DatabaseTester $I): void
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - parameters array string');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === $I->getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $paginator = new Model(
            [
                'model'      => Invoices::class,
                'parameters' => [
                    'inv_cst_id >= 2',
                ],
                'limit'      => 5,
                'page'       => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(5, $page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(2, $page->getNext());
        $I->assertEquals(9, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());
    }

    /**
     * @param DatabaseTester $I
     *
     * @group mysql
     * @group sqlite
     * @group pgsql
     */
    public function paginatorAdapterModelPaginateEmpty(DatabaseTester $I)
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - empty');

        $paginator = new Model(
            [
                'model'      => Invoices::class,
                'parameters' => [
                    'inv_cst_id < -1',
                ],
                'limit'      => 5,
                'page'       => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(0, $page->getItems());
        $I->assertIsArray($page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(0, $page->getNext());
        $I->assertEquals(0, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());
    }

    /**
     * Tests Phalcon\Paginator\Adapter\QueryBuilder :: paginate()
     *
     * @param DatabaseTester $I
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
    public function paginatorAdapterModelPaginateView(DatabaseTester $I): void
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - set in view');

        $this->setDiService('view');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = ('sqlite' === $I->getDriver()) ? 'null' : 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $paginator = new Model(
            [
                'model' => Invoices::class,
                'limit' => 5,
                'page'  => 1,
            ]
        );

        $page = $paginator->paginate();
        $I->assertCount(5, $page->getItems());

        $view = $this->getService('view');
        $view->setVar('page', $page);

        $actual = $view->getVar('page');
        $I->assertInstanceOf(Repository::class, $actual);


        $view = $this->getService('view');
        $view->setVar('paginate', $paginator->paginate());

        $actual = $view->getVar('paginate');
        $I->assertInstanceOf(Repository::class, $actual);
    }

    /**
     * @param DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-12-26
     * @issue  https://github.com/phalcon/cphalcon/issues/16471
     *
     * @group mysql
     * @group pgsql
     */
    public function paginatorAdapterModelPaginateWithOrder(DatabaseTester $I)
    {
        $I->wantToTest('Paginator\Adapter\Model - paginate() - with order');

        /** @var PDO $connection */
        $connection = $I->getConnection();
        $migration  = new InvoicesMigration($connection);
        $invId      = 'default';

        $this->insertDataInvoices($migration, 17, $invId, 2, 'ccc');
        $this->insertDataInvoices($migration, 11, $invId, 3, 'aaa');
        $this->insertDataInvoices($migration, 31, $invId, 1, 'aaa');
        $this->insertDataInvoices($migration, 15, $invId, 2, 'bbb');

        $paginator = new Model(
            [
                'model'      => Invoices::class,
                'parameters' => [
                    'inv_cst_id >= 2',
                    'order' => 'inv_cst_id'
                ],
                'limit'      => 5,
                'page'       => 1,
            ]
        );

        // First Page
        $page = $paginator->paginate();

        $I->assertInstanceOf(Repository::class, $page);

        $I->assertCount(5, $page->getItems());
        $I->assertEquals(1, $page->getPrevious());
        $I->assertEquals(2, $page->getNext());
        $I->assertEquals(9, $page->getLast());
        $I->assertEquals(5, $page->getLimit());
        $I->assertEquals(1, $page->getCurrent());
    }
}
