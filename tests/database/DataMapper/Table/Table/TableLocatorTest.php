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

namespace Phalcon\Tests\Database\DataMapper\Table\Table;

use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Table\Exception\TableClassMissingException;
use Phalcon\DataMapper\Table\TableLocator;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesTable;

final class TableLocatorTest extends AbstractDatabaseTestCase
{
    private TableLocator $locator;

    public function setUp(): void
    {
        parent::setUp();

        $connection    = self::getConnection();
        $this->locator = TableLocator::new($connection);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGet(): void
    {
        $actual = $this->locator->get(InvoicesTable::class);
        $this->assertInstanceOf(InvoicesTable::class, $actual);

        $twice = $this->locator->get(InvoicesTable::class);
        $this->assertInstanceOf(InvoicesTable::class, $twice);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetConnectionLocator(): void
    {
        $class  = ConnectionLocator::class;
        $actual = $this->locator->getConnectionLocator();
        $this->assertInstanceOf($class, $actual);
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testGetUnknownThrowsException(): void
    {
        $this->expectException(TableClassMissingException::class);
        $this->expectExceptionMessage(
            'Table class [other_table] does not exist '
            . 'or does not extend AbstractTable'
        );

        $actual = $this->locator->get('other_table');
    }

    /**
     * @return void
     *
     * @group mysql
     */
    public function testHas(): void
    {
        $actual = $this->locator->has(InvoicesTable::class);
        $this->assertTrue($actual);

        $actual = $this->locator->has('other_class');
        $this->assertFalse($actual);
    }
}
