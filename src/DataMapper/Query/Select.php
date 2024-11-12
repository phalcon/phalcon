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

use BadMethodCallException;
use Generator as Gen;
use PDO;
use Phalcon\DataMapper\Query\Traits\QueryTrait;
use Phalcon\DataMapper\Statement\Select as SelectStatement;

use function array_merge;

/**
 * Select Query
 *
 * @method int    fetchAffected()
 * @method array  fetchAll()
 * @method array  fetchAssoc()
 * @method array  fetchColumn(int $column = 0)
 * @method array  fetchGroup(int $flags = PDO::FETCH_ASSOC)
 * @method object fetchObject(string $class = "stdClass", array $arguments = [])
 * @method array  fetchObjects(string $class = "stdClass", array $arguments = [])
 * @method array  fetchOne()
 * @method array  fetchPairs()
 * @method array  fetchUnique()
 * @method mixed  fetchValue()
 * @method Gen    yieldAll()
 * @method Gen    yieldAssoc()
 * @method Gen    yieldColumn()
 * @method Gen    yieldObjects(string $class = 'stdClass', array $arguments = [])
 * @method Gen    yieldPairs()
 * @method Gen    yieldUnique()
 */
class Select extends SelectStatement
{
    use QueryTrait;

    /**
     * Proxied methods to the connection
     *
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        $proxied = [
            'fetchAffected' => true,
            'fetchAll'      => true,
            'fetchAssoc'    => true,
            'fetchColumn'   => true,
            'fetchGroup'    => true,
            'fetchObject'   => true,
            'fetchObjects'  => true,
            'fetchOne'      => true,
            'fetchPairs'    => true,
            'fetchUnique'   => true,
            'fetchValue'    => true,
            'yieldAffected' => true,
            'yieldAll'      => true,
            'yieldAssoc'    => true,
            'yieldColumn'   => true,
            'yieldObjects'  => true,
            'yieldPairs'    => true,
            'yieldUnique'   => true,
        ];

        if (isset($proxied[$method])) {
            $params = array_merge(
                [
                    $this->getStatement(),
                    $this->getBindValues(),
                ],
                $params
            );

            return $this->connection->$method(...$params);
        }

        throw new BadMethodCallException(
            "Unknown method: [" . $method . "]"
        );
    }
}
