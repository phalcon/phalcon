<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\InvoicesWithEvents;

use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesRow;

/**
 * @property $inv_id          int(10) auto_increment primary key
 * @property $inv_cst_id      int(10)      null
 * @property $inv_status_flag tinyint(1)   null
 * @property $inv_title       varchar(100) null
 * @property $inv_total       float(10, 2) null
 * @property $inv_created_at  datetime     null
 */
class InvoicesWithEventsRow extends InvoicesRow
{
}
