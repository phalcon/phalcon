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
 * The foreign key message is an int, as from a configuration file.
 */
class OrdersProductsIntMessage extends Model
{
    public function initialize()
    {
        $this->setSource('co_orders_x_products');

        $this->belongsTo(
            'oxp_ord_id',
            OrdersRelations::class,
            'ord_id',
            [
                'foreignKey' => [
                    'message' => 1001,
                ],
            ]
        );
    }
}
