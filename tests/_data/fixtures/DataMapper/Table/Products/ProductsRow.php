<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Products;

use Phalcon\DataMapper\Table\AbstractRow;

/**
 * @property $prd_id          int(10) auto_increment primary key
 * @property $prd_name        varchar(70) null
 */
class ProductsRow extends AbstractRow
{
    protected array $store = [
        'prd_id'   => null,
        'prd_name' => null,
    ];
}
