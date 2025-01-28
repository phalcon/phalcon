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
 * @link    https://github.com/atlasphp/Atlas.Info
 * @license https://github.com/atlasphp/Atlas.Info/blob/2.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Info\Adapter;

use Phalcon\DataMapper\Pdo\Connection;

use function in_array;
use function strtolower;
use function strtoupper;

/**
 * @phpstan-type ColumnDefinitionSql = array{
 *     _name: string,
 *     _type: string,
 *     _size?: int,
 *     _scale?: int,
 *     _notnull: bool,
 *     _default: mixed,
 *     _autoinc: bool,
 *     _primary: bool,
 *     _options: mixed
 * }
 *
 * @phpstan-type ColumnDefinition = array{
 *     name: string,
 *     type: string,
 *     size: int|null,
 *     scale: int|null,
 *     notnull: bool,
 *     default: mixed,
 *     autoinc: bool,
 *     primary: bool,
 *     options: mixed
 * }
 */
abstract class AbstractAdapter
{
    /**
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection
    ) {
    }

    /**
     * Process the default value based on the type and return the correct type
     * back
     *
     * @param mixed  $defaultValue
     * @param string $type
     *
     * @return mixed
     */
    protected function processDefault(mixed $defaultValue, string $type): mixed
    {
        $type         = strtolower($type);
        $floatTypes   = ['decimal', 'double', 'float', 'numeric', 'real'];
        $keywordTypes = ['CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP'];

        if (
            null === $defaultValue ||
            true === in_array(strtoupper((string)$defaultValue), $keywordTypes)
        ) {
            return null;
        }

        return match (true) {
            str_contains($type, 'int')   => (int)$defaultValue,
            in_array($type, $floatTypes) => (float)$defaultValue,
            default                      => $defaultValue,
        };
    }
}
