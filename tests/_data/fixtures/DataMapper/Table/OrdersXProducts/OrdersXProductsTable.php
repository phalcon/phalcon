<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts;

use Phalcon\DataMapper\Table\AbstractTable;

/**
 *
 * @method OrdersXProductsRow|null    fetchRow(mixed $primaryVal)
 * @method OrdersXProductsRow[]       fetchRows(array $primaryVals)
 * @method OrdersXProductsTableSelect select(array $whereEquals = [])
 * @method OrdersXProductsRow         newRow(array $cols = [])
 * @method OrdersXProductsRow         newSelectedRow(array $cols)
 */
class OrdersXProductsTable extends AbstractTable
{
    public const AUTOINC_COLUMN = null;

    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS = [
        'oxp_ord_id'   => [
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
            'name'            => 'oxp_ord_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'oxp_prd_id'   => [
            'afterField'      => 'inv_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => true,
            'isFirst'         => true,
            'isNotNull'       => true,
            'isNumeric'       => true,
            'isPrimary'       => true,
            'isUnsigned'      => false,
            'name'            => 'oxp_prd_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'oxp_quantity' => [
            'afterField'      => 'oxp_prd_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => true,
            'isFirst'         => true,
            'isNotNull'       => true,
            'isNumeric'       => true,
            'isPrimary'       => true,
            'isUnsigned'      => false,
            'name'            => 'oxp_quantity',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
    ];

    public const COLUMN_NAMES = [
        'oxp_ord_id',
        'oxp_prd_id',
        'oxp_quantity',
    ];

    public const COMPOSITE_KEY = true;

    public const DRIVER = 'mysql';

    public const NAME = 'co_orders_x_products';

    public const PRIMARY_KEY = [
        'oxp_ord_id',
        'oxp_prd_id',
    ];

    public const ROW_CLASS = OrdersXProductsRow::class;
}
