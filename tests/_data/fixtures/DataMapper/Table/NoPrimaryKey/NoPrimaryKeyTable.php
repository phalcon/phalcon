<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\NoPrimaryKey;

use Phalcon\DataMapper\Table\AbstractTable;

/**
 *
 * @method NoPrimaryKeyRow|null    fetchRow(mixed $primaryVal)
 * @method NoPrimaryKeyRow[]       fetchRows(array $primaryVals)
 * @method NoPrimaryKeyTableSelect select(array $whereEquals = [])
 * @method NoPrimaryKeyRow         newRow(array $cols = [])
 * @method NoPrimaryKeyRow         newSelectedRow(array $cols)
 */
class NoPrimaryKeyTable extends AbstractTable
{
    public const AUTOINC_COLUMN = null;

    public const AUTOINC_SEQUENCE = null;

    public const COLUMNS = [
        'nokey_id'   => [
            'afterField'      => null,
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => true,
            'isNotNull'       => true,
            'isNumeric'       => true,
            'isPrimary'       => false,
            'isUnsigned'      => false,
            'name'            => 'nokey_id',
            'options'         => null,
            'scale'           => 0,
            'size'            => 10,
            'type'            => 'int',
        ],
        'prd_name' => [
            'afterField'      => 'nokey_id',
            'comment'         => '',
            'default'         => null,
            'hasDefault'      => false,
            'isAutoIncrement' => false,
            'isFirst'         => false,
            'isNotNull'       => false,
            'isNumeric'       => false,
            'isPrimary'       => false,
            'isUnsigned'      => null,
            'name'            => 'nokey_name',
            'options'         => null,
            'scale'           => null,
            'size'            => 100,
            'type'            => 'varchar',
        ],
    ];

    public const COLUMN_NAMES = [
        'nokey_id',
        'nokey_name',
    ];

    public const COMPOSITE_KEY = false;

    public const DRIVER = 'mysql';

    public const NAME = 'no_primary_key';

    public const PRIMARY_KEY = [];

    public const ROW_CLASS = NoPrimaryKeyRow::class;
}
