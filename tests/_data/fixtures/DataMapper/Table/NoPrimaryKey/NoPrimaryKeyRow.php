<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures\DataMapper\Table\NoPrimaryKey;

use Phalcon\DataMapper\Table\AbstractRow;

/**
 * @property $nokey_id          int(10)
 * @property $nokey_name        varchar(100)
 */
class NoPrimaryKeyRow extends AbstractRow
{
    protected array $store = [
        'nokey_id'   => null,
        'nokey_name' => null,
    ];
}
