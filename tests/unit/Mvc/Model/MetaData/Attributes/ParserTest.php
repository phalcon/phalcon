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

namespace Phalcon\Tests\Unit\Mvc\Model\MetaData\Attributes;

use Phalcon\Db\Column;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Attributes;
use Phalcon\Tests\Fixtures\models\AttributesModel;
use Phalcon\Tests\UnitTestCase;

class ParserTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Model\MetaData\Attributes :: getMetaData
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-02-07
     */
    public function testAttributesGetMetaData(): void
    {
        $di = new Di();
        $di->setShared('modelsManager', new Manager());

        $parser = new Attributes();
        $result = $parser->getMetaData(new AttributesModel(), $di);

        $this->assertEquals(
            [
                MetaData::MODELS_ATTRIBUTES => [
                    'inv_id',
                    'inv_cst_id',
                    'inv_status_flag',
                    'inv_title',
                    'inv_total',
                    'inv_created_at',
                    'inv_created_by',
                    'inv_updated_at',
                    'inv_updated_by',
                ],

                MetaData::MODELS_PRIMARY_KEY => [
                    'inv_id',
                ],

                MetaData::MODELS_NON_PRIMARY_KEY => [
                    'inv_cst_id',
                    'inv_status_flag',
                    'inv_title',
                    'inv_total',
                    'inv_created_at',
                    'inv_created_by',
                    'inv_updated_at',
                    'inv_updated_by',
                ],

                MetaData::MODELS_NOT_NULL => [
                    'inv_id',
                    'inv_cst_id',
                    'inv_status_flag',
                    'inv_title',
                    'inv_total',
                    'inv_created_at',
                    'inv_created_by',
                    'inv_updated_at',
                    'inv_updated_by',
                ],

                MetaData::MODELS_DATA_TYPES => [
                    'inv_id'          => Column::TYPE_INTEGER,
                    'inv_cst_id'      => Column::TYPE_INTEGER,
                    'inv_status_flag' => Column::TYPE_INTEGER,
                    'inv_title'       => Column::TYPE_VARCHAR,
                    'inv_total'       => Column::TYPE_FLOAT,
                    'inv_created_at'  => Column::TYPE_DATETIME,
                    'inv_created_by'  => Column::TYPE_INTEGER,
                    'inv_updated_at'  => Column::TYPE_DATETIME,
                    'inv_updated_by'  => Column::TYPE_INTEGER,
                ],

                MetaData::MODELS_DATA_TYPES_NUMERIC => [
                    'inv_id'          => true,
                    'inv_cst_id'      => true,
                    'inv_status_flag' => true,
                    'inv_total'       => true,
                    'inv_created_by'  => true,
                    'inv_updated_by'  => true,
                ],

                MetaData::MODELS_IDENTITY_COLUMN => 'inv_id',

                MetaData::MODELS_DATA_TYPES_BIND => [
                    'inv_id'          => Column::BIND_PARAM_INT,
                    'inv_cst_id'      => Column::BIND_PARAM_INT,
                    'inv_status_flag' => Column::BIND_PARAM_INT,
                    'inv_title'       => Column::BIND_PARAM_STR,
                    'inv_total'       => Column::BIND_PARAM_DECIMAL,
                    'inv_created_at'  => Column::BIND_PARAM_STR,
                    'inv_created_by'  => Column::BIND_PARAM_INT,
                    'inv_updated_at'  => Column::BIND_PARAM_STR,
                    'inv_updated_by'  => Column::BIND_PARAM_INT,
                ],

                MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT => [
                    'inv_created_at' => true,
                    'inv_created_by' => true,
                    'inv_updated_at' => true,
                    'inv_updated_by' => true,
                ],

                MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE => [
                    'inv_created_at' => true,
                    'inv_created_by' => true,
                    'inv_updated_at' => true,
                    'inv_updated_by' => true,
                ],

                MetaData::MODELS_DEFAULT_VALUES => [
                    'inv_status_flag' => 0,
                ],

                MetaData::MODELS_EMPTY_STRING_VALUES => [
                    'inv_created_at' => true,
                    'inv_updated_at' => true,
                ],
            ],
            $result,
        );
    }

    /**
     * Tests Phalcon\Model\MetaData\Attributes :: getColumnMap
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-02-07
     */
    public function testAttributesGetColumnMap(): void
    {
        $di = new Di();
        $di->setShared('modelsManager', new Manager());

        $parser = new Attributes();
        $result = $parser->getColumnMaps(new AttributesModel(), $di);

        $this->assertEquals(
            [
                [
                    "inv_id"          => "id",
                    "inv_cst_id"      => "inv_cst_id",
                    "inv_status_flag" => "inv_status_flag",
                    "inv_title"       => "inv_title",
                    "inv_total"       => "inv_total",
                    "inv_created_at"  => "inv_created_at",
                    "inv_created_by"  => "inv_created_by",
                    "inv_updated_at"  => "inv_updated_at",
                    "inv_updated_by"  => "inv_updated_by",
                ],
                [
                    "id"              => "inv_id",
                    "inv_cst_id"      => "inv_cst_id",
                    "inv_status_flag" => "inv_status_flag",
                    "inv_title"       => "inv_title",
                    "inv_total"       => "inv_total",
                    "inv_created_at"  => "inv_created_at",
                    "inv_created_by"  => "inv_created_by",
                    "inv_updated_at"  => "inv_updated_at",
                    "inv_updated_by"  => "inv_updated_by",
                ],
            ],
            $result,
        );
    }
}
