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

namespace Phalcon\Tests\Support\Models;

use Phalcon\Mvc\Model;

/**
 * Model with a column map that has an int value (2024) for `cst_name_last`.
 * The column has a default value.
 */
class CustomersDefaultsIntColumnMap extends Model
{
    public $cst_id;
    public $cst_name_first;
    public $cst_status_flag;

    public function columnMap()
    {
        return [
            'cst_id'          => 'cst_id',
            'cst_status_flag' => 'cst_status_flag',
            'cst_name_last'   => 2024,
            'cst_name_first'  => 'cst_name_first',
        ];
    }

    public function initialize()
    {
        $this->setSource('co_customers_defaults');
        $this->keepSnapshots(true);
    }
}
