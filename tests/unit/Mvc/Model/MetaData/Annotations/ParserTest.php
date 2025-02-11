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

namespace Phalcon\Tests\Unit\Mvc\Model\MetaData\Annotations;

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Db\Column;
use Phalcon\Di\Di;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Fixtures\models\AnnotationsModel;
use Phalcon\Tests\Fixtures\models\AnnotationsNoChangeModel;

class ParserTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Model\MetaData\Annotations :: getMetaData
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-02-07
     */
    public function testAnnotationsGetMetaData(): void
    {
        $di = new Di();
        $di->setShared('modelsManager', new Manager());
        $di->setShared(
            'annotations',
            new \Phalcon\Annotations\Annotations(
                new Memory(
                    new SerializerFactory()
                )
            )
        );

        $parser = new Annotations();
        $result = $parser->getMetaData(new AnnotationsModel(null, $di), $di);

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
                    'inv_title'       => false,
                    'inv_total'       => true,
                    'inv_created_at'  => false,
                    'inv_created_by'  => true,
                    'inv_updated_at'  => false,
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
     * Tests Phalcon\Model\MetaData\Annotations :: getColumnMap
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-02-07
     */
    public function testAnnotationsGetColumnMap(): void
    {
        $di = new Di();
        $di->setShared('modelsManager', new Manager());
        $di->setShared(
            'annotations',
            new \Phalcon\Annotations\Annotations(
                new Memory(
                    new SerializerFactory()
                )
            )
        );

        $parser = new Annotations();
        $result = $parser->getColumnMaps(new AnnotationsModel(null, $di), $di);

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

    /**
     * Tests Phalcon\Model\MetaData\Annotations :: getColumnMap
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2025-02-07
     */
    public function testAnnotationsGetColumnMapNoChange(): void
    {
        $di = new Di();
        $di->setShared('modelsManager', new Manager());
        $di->setShared(
            'annotations',
            new \Phalcon\Annotations\Annotations(
                new Memory(
                    new SerializerFactory()
                )
            )
        );

        $parser = new Annotations();
        $result = $parser->getColumnMaps(new AnnotationsNoChangeModel(null, $di), $di);

        $this->assertEquals(
            [
                null,
                null,
            ],
            $result,
        );
    }
}
