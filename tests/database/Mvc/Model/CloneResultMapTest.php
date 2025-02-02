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

namespace Phalcon\Tests\Database\Mvc\Model;

use PDO;
use Phalcon\Mvc\Model;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\InvoicesMap;

final class CloneResultMapTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * @return array
     */
    public static function modelDataProvider(): array
    {
        return [
            [
                '1',
                '42',
                '1',
                'Test title',
                '3.14',
                '2020-10-05 20:43',
            ],
            [
                1,
                42,
                1,
                'Test title',
                3.14,
                '2020-10-05 20:43',
            ],
        ];
    }

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        /** @var PDO $connection */
        $connection = self::getConnection();
        (new InvoicesMigration($connection));
    }

    /**
     * Tests Phalcon\Mvc\Model :: cloneResultMap()
     *
     * @dataProvider modelDataProvider
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-10-05
     *
     * @group mysql
     */
    public function testMvcModelCloneResultMap(
        int | string $invId,
        int | string $invCstId,
        int | string $invStatusFlag,
        string $invTitle,
        float | string $invTotal,
        string $invCreatedAt
    ): void {
        $base = new InvoicesMap();

        /**
         * @var Model\MetaData $metaData
         */
        $metaData = $base->getModelsMetaData();

        $columnMap = $metaData->getColumnMap($base);

        $data = [
            'inv_id'          => $invId,
            'inv_cst_id'      => $invCstId,
            'inv_status_flag' => $invStatusFlag,
            'inv_title'       => $invTitle,
            'inv_total'       => $invTotal,
            'inv_created_at'  => $invCreatedAt,
        ];

        /**
         * @var InvoicesMap $invoice
         */
        $invoice = Model::cloneResultMap(
            $base,
            $data,
            $columnMap
        );

        $expected = array_combine(
            $columnMap,
            $data
        );

        $this->assertEquals(
            $expected,
            $invoice->toArray()
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: cloneResultMap() with casting
     *
     * @dataProvider modelDataProvider
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-10-05
     *
     * @group mysql
     * @group pgsql
     */
    public function testMvcModelCloneResultMapWithCasting(
        int | string $invId,
        int | string $invCstId,
        int | string $invStatusFlag,
        string $invTitle,
        float | string $invTotal,
        string $invCreatedAt
    ): void {
        $base = new InvoicesMap();

        /**
         * @var Model\MetaData $metaData
         */
        $metaData = $base->getModelsMetaData();

        $columnMap = $metaData->getColumnMap($base);

        /**
         * $dataTypes = [
         *   'inv_id'          => Column::TYPE_INTEGER,
         *   'inv_cst_id'      => Column::TYPE_INTEGER,
         *   'inv_status_flag' => Column::TYPE_TINYINTEGER,
         *   'inv_title'       => Column::TYPE_VARCHAR,
         *   'inv_total'       => Column::TYPE_FLOAT,
         *   'inv_created_at'  => Column::TYPE_DATETIME
         * ];
         */
        $dataTypes = $metaData->getDataTypes($base);

        /**
         * Assemble typed column map
         */
        foreach ($columnMap as $mappedField => $field) {
            $typedColumnMap[$mappedField] = [
                $field,
                $dataTypes[$mappedField],
            ];
        }

        /**
         * @var InvoicesMap $invoice
         */
        $invoice = Model::cloneResultMap(
            $base,
            [
                'inv_id'          => $invId,
                'inv_cst_id'      => $invCstId,
                'inv_status_flag' => $invStatusFlag,
                'inv_title'       => $invTitle,
                'inv_total'       => $invTotal,
                'inv_created_at'  => $invCreatedAt,
            ],
            $typedColumnMap
        );

        $this->assertIsInt($invoice->id);
        $this->assertEquals((int)$invId, $invoice->id);
        $this->assertIsInt($invoice->cst_id);
        $this->assertEquals((int)$invCstId, $invoice->cst_id);
        $this->assertIsInt($invoice->status_flag);
        $this->assertEquals((int)$invStatusFlag, $invoice->status_flag);
        $this->assertIsString($invoice->title);
        $this->assertEquals($invTitle, $invoice->title);
        $this->assertIsFloat($invoice->total);
        $this->assertIsString($invoice->created_at);
        $this->assertEquals($invCreatedAt, $invoice->created_at);
    }
}
