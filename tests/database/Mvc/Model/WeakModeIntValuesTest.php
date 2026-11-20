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
use Phalcon\Mvc\Model\Hydration\CaseInsensitiveColumnMap;
use Phalcon\Mvc\Model\MetaData\Exceptions\MetaDataDirectoryNotWritable;
use Phalcon\Mvc\Model\MetaData\Stream;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Talon\Talon;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Migrations\InvoicesMigration;
use Phalcon\Tests\Support\Migrations\OrdersMigration;
use Phalcon\Tests\Support\Migrations\OrdersProductsMigration;
use Phalcon\Tests\Support\Migrations\ProductsMigration;
use Phalcon\Tests\Support\Models\Invoices;
use Phalcon\Tests\Support\Models\OrdersProductsIntMessage;
use Phalcon\Tests\Support\Models\ProductsIntMessage;
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
