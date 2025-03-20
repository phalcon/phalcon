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
 * @link    https://github.com/atlasphp/Atlas.Table
 * @license https://github.com/atlasphp/Atlas.Table/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Table\Exception;

/**
 * Exception when an operation is to be performed on a table without a primary
 * key
 */
class PrimaryValueMissingException extends Exception
{
    /**
     * @param string $column
     */
    public function __construct(string $column)
    {
        parent::__construct(
            "The scalar value for primary key [$column] is missing."
        );
    }
}
