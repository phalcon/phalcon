<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\NoPrimaryKey;

use Phalcon\DataMapper\Table\AbstractTableSelect;

/**
 * @method NoPrimaryKeyRow|null fetchRow()
 * @method NoPrimaryKeyRow[]    fetchRows()
 */
class NoPrimaryKeyTableSelect extends AbstractTableSelect
{
}
