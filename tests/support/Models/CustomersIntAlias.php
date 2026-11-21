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
 * Model with the numeric relation alias `2024`.
 */
class CustomersIntAlias extends Model
{
    public $cst_id;
    public $cst_name_first;
    public $cst_name_last;
    public $cst_status_flag;

    public function initialize()
    {
        $this->setSource('co_customers');

        $this->hasMany(
            'cst_id',
            Invoices::class,
            'inv_cst_id',
            [
                'alias' => '2024',
            ]
        );
    }
}
