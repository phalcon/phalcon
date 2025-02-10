<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Products;

use Phalcon\DataMapper\Table\AbstractTable;

/**
 *
 * @method ProductsRow|null    fetchRow(mixed $primaryVal)
 * @method ProductsRow[]       fetchRows(array $primaryVals)
 * @method ProductsTableSelect select(array $whereEquals = [])
 * @method ProductsRow         newRow(array $cols = [])
 * @method ProductsRow         newSelectedRow(array $cols)
 */
class ProductsTable extends AbstractTable
{
    public const AUTOINC_COLUMN = 'inv_id';

    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS = [
        'prd_id'   => [
            'afterField'      => null,
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => true,
            'isFirst'         => true,
            'isNotNull'       => true,
            'isNumeric'       => true,
            'isPrimary'       => true,
            'isUnsigned'      => false,
            'name'            => 'prd_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'prd_name' => [
            'afterField'      => 'prd_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => false,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'prd_name',
            'options'         => null,
            'scale'           => null,
            'size'            => 70,
            'type'            => 'varchar',
        ],
    ];

    public const COLUMN_NAMES = [
        'prd_id',
        'prd_name',
    ];

    public const COMPOSITE_KEY = false;

    public const DRIVER = 'mysql';

    public const NAME = 'co_products';

    public const PRIMARY_KEY = [
        'prd_id',
    ];

    public const ROW_CLASS = ProductsRow::class;
}
