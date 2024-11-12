<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Query
 * @license https://github.com/atlasphp/Atlas.Query/blob/2.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Query;

use Phalcon\DataMapper\Query\Traits\QueryTrait;
use Phalcon\DataMapper\Statement\Update as UpdateStatement;

/**
 * Update Query
 */
class Update extends UpdateStatement
{
    use QueryTrait;
}
