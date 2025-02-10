<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Orders;

use Phalcon\DataMapper\Table\AbstractTable;

/**
 *
 * @method OrdersRow|null    fetchRow(mixed $primaryVal)
 * @method OrdersRow[]       fetchRows(array $primaryVals)
 * @method OrdersTableSelect select(array $whereEquals = [])
 * @method OrdersRow         newRow(array $cols = [])
 * @method OrdersRow         newSelectedRow(array $cols)
 */
class OrdersTable extends AbstractTable
{
    public const AUTOINC_COLUMN = 'inv_id';

    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS = [
        'ord_id'   => [
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
            'name'            => 'ord_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'ord_name' => [
            'afterField'      => 'ord_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => false,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'ord_name',
            'options'         => null,
            'scale'           => null,
            'size'            => 70,
            'type'            => 'varchar',
        ],
    ];

    public const COLUMN_NAMES = [
        'ord_id',
        'ord_name',
    ];

    public const COMPOSITE_KEY = false;

    public const DRIVER = 'mysql';

    public const NAME = 'co_orders';

    public const PRIMARY_KEY = [
        'ord_id',
    ];

    public const ROW_CLASS = OrdersRow::class;
}
