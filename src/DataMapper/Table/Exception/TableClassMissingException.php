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
 * Exception when a table class does not exist or does not extend AbstractTable
 */
class TableClassMissingException extends Exception
{
    /**
     * @param string $tableClass
     */
    public function __construct(string $tableClass)
    {
        parent::__construct(
            'Table class [' . $tableClass
            . '] does not exist or does not extend AbstractTable.'
        );
    }
}
