<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Orders;

use Phalcon\DataMapper\Table\AbstractRow;

/**
 * @property $ord_id          int(10) auto_increment primary key
 * @property $ord_title       varchar(70) null
 */
class OrdersRow extends AbstractRow
{
    protected array $store = [
        'ord_id'   => null,
        'ord_name' => null,
    ];
}
