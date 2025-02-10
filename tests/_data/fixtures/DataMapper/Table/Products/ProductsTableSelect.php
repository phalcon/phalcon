<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\Products;

use Phalcon\DataMapper\Table\AbstractTableSelect;

/**
 * @method ProductsRow|null fetchRow()
 * @method ProductsRow[]    fetchRows()
 */
class ProductsTableSelect extends AbstractTableSelect
{
}
