<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\OrdersXProducts;

use Phalcon\DataMapper\Table\AbstractRow;

/**
 * @property $oxp_ord_id      int(10) unsigned
 * @property $oxp_prd_id      int(10) unsigned
 * @property $oxp_quantity    int(10) unsigned
 */
class OrdersXProductsRow extends AbstractRow
{
    protected array $store = [
        'oxp_ord_id'   => null,
        'oxp_prd_id'   => null,
        'oxp_quantity' => null,
    ];
}
