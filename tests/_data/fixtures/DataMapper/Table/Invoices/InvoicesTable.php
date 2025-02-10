<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Invoices;

use Phalcon\DataMapper\Table\AbstractTable;

/**
 *
 * @method InvoicesRow|null    fetchRow(mixed $primaryVal)
 * @method InvoicesRow[]       fetchRows(array $primaryVals)
 * @method InvoicesTableSelect select(array $whereEquals = [])
 * @method InvoicesRow         newRow(array $cols = [])
 * @method InvoicesRow         newSelectedRow(array $cols)
 */
class InvoicesTable extends AbstractTable
{
    public const AUTOINC_COLUMN = 'inv_id';

    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS = [
        'inv_id'          => [
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
            'name'            => 'inv_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'inv_cst_id'      => [
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
            'name'            => 'inv_cst_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'inv_status_flag' => [
            'afterField'      => 'inv_cst_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => true,
            'isFirst'         => true,
            'isNotNull'       => true,
            'isNumeric'       => true,
            'isPrimary'       => true,
            'isUnsigned'      => false,
            'name'            => 'inv_status_flag',
            'options'         => null,
            'scale'           => 0,
            'size'            => 3,
            'type'            => 'tinyint',
        ],
        'inv_title'       => [
            'afterField'      => 'inv_status_flag',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => false,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'inv_title',
            'options'         => null,
            'scale'           => null,
            'size'            => 100,
            'type'            => 'varchar',
        ],
        'inv_total'       => [
            'afterField'      => 'inv_title',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => true,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'inv_total',
            'options'         => null,
            'scale'           => 2,
            'size'            => 10,
            'type'            => 'float',
        ],
        'inv_created_at'  => [
            'afterField'      => 'inv_total',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => false,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'inv_created_at',
            'options'         => null,
            'scale'           => null,
            'size'            => null,
            'type'            => 'datetime',
        ],
    ];

    public const COLUMN_NAMES = [
        'inv_id',
        'inv_cst_id',
        'inv_status_flag',
        'inv_title',
        'inv_total',
        'inv_created_at',
    ];

    public const COMPOSITE_KEY = false;

    public const DRIVER = 'mysql';

    public const NAME = 'co_invoices';

    public const PRIMARY_KEY = [
        'inv_id',
    ];

    public const ROW_CLASS = InvoicesRow::class;
}
