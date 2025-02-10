<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\InvoicesWithEvents;

use Phalcon\Tests\Fixtures\DataMapper\Table\Invoices\InvoicesTable;

/**
 *
 * @method InvoicesWithEventsRow|null    fetchRow(mixed $primaryVal)
 * @method InvoicesWithEventsRow[]       fetchRows(array $primaryVals)
 * @method InvoicesWithEventsTableSelect select(array $whereEquals = [])
 * @method InvoicesWithEventsRow         newRow(array $cols = [])
 * @method InvoicesWithEventsRow         newSelectedRow(array $cols)
 */
class InvoicesWithEventsTable extends InvoicesTable
{
}
