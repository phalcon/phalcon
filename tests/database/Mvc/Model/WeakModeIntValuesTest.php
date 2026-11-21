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

namespace Phalcon\Tests\Database\Mvc\Model;

use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Cache;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Exceptions\UnknownEagerRelation;
use Phalcon\Mvc\Model\Hydration\CaseInsensitiveColumnMap;
use Phalcon\Mvc\Model\MetaData\Exceptions\MetaDataDirectoryNotWritable;
use Phalcon\Mvc\Model\MetaData\Stream;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Query\Exceptions\UnknownModelOrAlias;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Talon\Talon;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\CustomersDefaultsMigration;
use Phalcon\Tests\Support\Migrations\CustomersMigration;
use Phalcon\Tests\Support\Migrations\InvoicesMigration;
use Phalcon\Tests\Support\Migrations\OrdersMigration;
use Phalcon\Tests\Support\Migrations\OrdersProductsMigration;
use Phalcon\Tests\Support\Migrations\ProductsMigration;
use Phalcon\Tests\Support\Models\Customers;
use Phalcon\Tests\Support\Models\CustomersDefaultsIntColumnMap;
use Phalcon\Tests\Support\Models\CustomersIntAlias;
use Phalcon\Tests\Support\Models\Invoices;
use Phalcon\Tests\Support\Models\OrdersProductsIntMessage;
use Phalcon\Tests\Support\Models\OrdersRelations;
use Phalcon\Tests\Support\Models\ProductsIntMessage;
use Phalcon\Tests\Support\Models\ProductsRelations;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\Group;

use function property_exists;
use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string. The getters of cphalcon return the
 * int with no change, thus the tests use assertEquals for them.
 *
 * @issue https://github.com/phalcon/phalcon/issues/884
 */
final class WeakModeIntValuesTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $migration = new InvoicesMigration(self::getPdoConnection());
        $migration->insert(1, 1, 0, 'Invoice One');
        $migration->insert(2, 1, 0, 'Invoice Two');
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelAssignIntColumnMapValue(): void
    {
        /**
         * cphalcon gives a warning and does not set a property with a numeric
         * name
         */
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $customer = new CustomersDefaultsIntColumnMap();
            $customer->assign([2024 => 'last', 'cst_name_first' => 'first']);
            $actual = $customer->toArray();
        } finally {
            restore_error_handler();
        }

        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertNull($actual[2024]);
        $this->assertSame('first', $actual['cst_name_first']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelCriteriaColumnsInt(): void
    {
        $criteria = new Criteria();
        $criteria->columns(1);

        $this->assertEquals(1, $criteria->getColumns());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelEagerLoadIntAlias(): void
    {
        $migration = new CustomersMigration(self::getPdoConnection());
        $migration->insert(1, 1, 'first', 'last');

        $customer = CustomersIntAlias::find(['eager' => ['2024']])->getFirst();

        $this->assertCount(2, $customer->getRelated('2024'));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelEagerLoadUnknownIntAlias(): void
    {
        $migration = new CustomersMigration(self::getPdoConnection());
        $migration->insert(1, 1, 'first', 'last');

        $this->expectException(UnknownEagerRelation::class);
        $this->expectExceptionMessage(
            "Model '" . Customers::class . "' does not define a relation with alias '2024'"
        );

        Customers::find(['eager' => ['2024']]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelFindCacheKeyInt(): void
    {
        $this->container->setShared(
            'modelsCache',
            new Cache(new Memory(new SerializerFactory()))
        );

        $invoices = Invoices::find(['cache' => ['key' => 123, 'lifetime' => 60]]);
        $this->assertCount(2, $invoices);

        $invoices = Invoices::find(['cache' => ['key' => 123, 'lifetime' => 60]]);
        $this->assertCount(2, $invoices);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelFindColumnsAndOrderInt(): void
    {
        $this->assertCount(2, Invoices::find(['columns' => 1]));
        $this->assertCount(2, Invoices::find(['order' => 1]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('sqlite')]
    public function testMvcModelForeignKeyMessageIntBelongsTo(): void
    {
        $connection = self::getPdoConnection();
        new OrdersMigration($connection);
        new ProductsMigration($connection);
        new OrdersProductsMigration($connection);

        $record               = new OrdersProductsIntMessage();
        $record->oxp_ord_id   = 999;
        $record->oxp_prd_id   = 1;
        $record->oxp_quantity = 1;

        $this->assertFalse($record->save());
        $this->assertEquals(1001, $record->getMessages()[0]->getMessage());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('sqlite')]
    public function testMvcModelForeignKeyMessageIntHasMany(): void
    {
        $connection = self::getPdoConnection();
        (new OrdersMigration($connection))->insert(1, 'order');
        (new ProductsMigration($connection))->insert(1, 'product');
        (new OrdersProductsMigration($connection))->insert(1, 1, 1);

        $product = ProductsIntMessage::findFirst(1);

        $this->assertFalse($product->delete());
        $this->assertEquals(1002, $product->getMessages()[0]->getMessage());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelGetChangedFieldsIntColumnMapValue(): void
    {
        $migration = new CustomersDefaultsMigration(self::getPdoConnection());
        $migration->insert(1, 1, 'first', 'last');

        /**
         * cphalcon gives a warning and does not set a property with a numeric
         * name
         */
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $customer = CustomersDefaultsIntColumnMap::findFirst(1);
        } finally {
            restore_error_handler();
        }

        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertSame([2024], $customer->getChangedFields());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelHydrationCaseInsensitiveColumnMapIntKey(): void
    {
        $this->assertSame(
            'id',
            CaseInsensitiveColumnMap::caseInsensitiveColumnMap([2024 => 'year', 'id' => 'id'], 'ID')
        );
        $this->assertEquals(
            2024,
            CaseInsensitiveColumnMap::caseInsensitiveColumnMap([2024 => 'year'], '2024')
        );
        $this->assertEquals(
            5,
            CaseInsensitiveColumnMap::caseInsensitiveColumnMap(['id' => 'id'], 5)
        );
        $this->assertEquals(5, CaseInsensitiveColumnMap::caseInsensitiveColumnMap([], 5));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelMetaDataReadMetaDataIndexInt(): void
    {
        $metaData = $this->container->getShared('modelsMetadata');
        $invoice  = new Invoices();
        $metaData->getAttributes($invoice);
        $metaData->writeMetaDataIndex($invoice, 99, 5);

        $this->assertEquals(5, $metaData->readMetaDataIndex($invoice, 99));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelMetaDataStreamIntKey(): void
    {
        $this->assertInstanceOf(Stream::class, new Stream(['metaDataDir' => 123]));

        $stream = new Stream(
            ['metaDataDir' => Talon::settings()->outputPath('no-such-dir/')]
        );

        $this->assertNull($stream->read(123));

        $this->expectException(MetaDataDirectoryNotWritable::class);
        $this->expectExceptionMessage('Meta-Data directory cannot be written');

        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $stream->write(123, []);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelQueryBuilderIntOptions(): void
    {
        $model = Invoices::class;

        $builder = new Builder(['models' => $model, 'columns' => 1]);
        $this->assertSame('SELECT 1 FROM [' . $model . ']', $builder->getPhql());

        $builder = (new Builder())->from($model)->columns(1);
        $this->assertSame('SELECT 1 FROM [' . $model . ']', $builder->getPhql());

        $builder = new Builder(['models' => $model, 'order' => 1]);
        $this->assertSame('SELECT [' . $model . '].* FROM [' . $model . '] ORDER BY 1', $builder->getPhql());

        $builder = (new Builder())->from($model)->orderBy(1);
        $this->assertSame('SELECT [' . $model . '].* FROM [' . $model . '] ORDER BY 1', $builder->getPhql());

        $builder = new Builder(['models' => $model, 'group' => 'inv_id', 'having' => 1]);
        $this->assertSame(
            'SELECT [' . $model . '].* FROM [' . $model . '] GROUP BY [inv_id] HAVING 1',
            $builder->getPhql()
        );
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelQueryDeleteIntAliasSelectAll(): void
    {
        $query = new Query(
            'DELETE FROM [' . Invoices::class . '] AS [2024] '
            . 'WHERE EXISTS (SELECT * FROM [' . Customers::class . ']) '
            . 'AND [2024].inv_id = 0',
            $this->container
        );

        /**
         * cphalcon gives a warning for the numeric alias
         */
        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $actual = $query->parse();
        } finally {
            restore_error_handler();
        }

        $this->assertIsArray($actual);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelQueryJoinIntAlias(): void
    {
        $migration = new CustomersMigration(self::getPdoConnection());
        $migration->insert(1, 1, 'first', 'last');

        $result = $this->container->get('modelsManager')->executeQuery(
            'SELECT c.cst_id FROM [' . CustomersIntAlias::class . '] c '
            . 'JOIN [' . Invoices::class . '] AS [2024]'
        );

        $this->assertCount(2, $result);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelQueryManyToManyJoinIntAlias(): void
    {
        $phql = 'SELECT o.ord_id FROM [' . OrdersRelations::class . '] o '
            . 'JOIN [' . ProductsRelations::class . '] AS [2025]';

        $this->expectException(UnknownModelOrAlias::class);
        $this->expectExceptionMessage(
            "Unknown model or alias '" . ProductsRelations::class . "' (11), when preparing: " . $phql
        );

        $this->container->get('modelsManager')->executeQuery($phql);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/899
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelSaveIntColumnMapValueDefault(): void
    {
        new CustomersDefaultsMigration(self::getPdoConnection());

        /**
         * cphalcon gives a warning and does not set a property with a numeric
         * name
         */
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $customer                 = new CustomersDefaultsIntColumnMap();
            $customer->cst_name_first = 'first';
            $actual                   = $customer->save();
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($actual);
        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertNull($customer->toArray()[2024]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelSetSyncNonString(): void
    {
        $invoice = new Invoices();

        $this->assertSame($invoice, $invoice->setSync(false));
        $this->assertSame($invoice, $invoice->setSync([1]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelUnserializeNumericPropertyName(): void
    {
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $invoice = new Invoices();
            $invoice->__unserialize(['attributes' => ['inv_id' => 1, '2024' => 'year']]);
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertEquals(1, $invoice->inv_id);
        $this->assertFalse(property_exists($invoice, '2024'));
    }
}
