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
class ProductsIntMessage extends Model
{
    public function initialize()
    {
        $this->setSource('co_products');

        $this->hasMany(
            'prd_id',
            OrdersProductsIntMessage::class,
            'oxp_prd_id',
            [
                'foreignKey' => [
                    'message' => 1002,
                ],
            ]
        );
    }
}
