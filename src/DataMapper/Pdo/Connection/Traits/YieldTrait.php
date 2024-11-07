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
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Pdo\Connection\Traits;

use Generator;
use PDO;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Exception\Exception;

use function current;

/**
 * Yield methods for the connection
 */
trait YieldTrait
{
    /**
     * Performs a query with bound values and returns the resulting
     * PDOStatement; array values will be passed through `quote()` and their
     * respective placeholders will be replaced in the query string. If the
     * profiler is enabled, the operation will be recorded.
     *
     * @param string $statement
     * @param array  $values
     *
     * @return PDOStatement
     * @throws Exception
     */
    abstract public function perform(
        string $statement,
        array $values = []
    ): PDOStatement;

    /**
     * Yield results using fetchAll
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldAll(string $statement, array $values = []): Generator
    {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * Yield results using fetchAssoc
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldAssoc(
        string $statement,
        array $values = []
    ): Generator {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $key = current($row);
            yield $key => $row;
        }
    }

    /**
     * Yield results using fetchColumn
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldColumn(
        string $statement,
        array $values = []
    ): Generator {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            yield $row[0];
        }
    }

    /**
     * Yield objects where the column values are mapped to object properties.
     *
     * Warning: PDO "injects property-values BEFORE invoking the constructor -
     * in other words, if your class initializes property-values to defaults
     * in the constructor, you will be overwriting the values injected by
     * fetchObject() !"
     * <https://www.php.net/manual/en/pdostatement.fetchobject.php#111744>
     *
     * @param string $statement
     * @param array  $values
     * @param string $class
     * @param array  $arguments
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldObjects(
        string $statement,
        array $values = [],
        string $class = 'stdClass',
        array $arguments = []
    ): Generator {
        $sth = $this->perform($statement, $values);

        if (empty($arguments)) {
            while ($instance = $sth->fetchObject($class)) {
                yield $instance;
            }
        } else {
            while ($instance = $sth->fetchObject($class, $arguments)) {
                yield $instance;
            }
        }
    }

    /**
     * Yield key-value pairs (key => value)
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldPairs(
        string $statement,
        array $values = []
    ): Generator {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            yield $row[0] => $row[1];
        }
    }

    /**
     * Yield results using `fetchAll` and `FETCH_UNIQUE`
     *
     * @param string $statement
     * @param array  $values
     *
     * @return Generator
     * @throws Exception
     */
    public function yieldUnique(
        string $statement,
        array $values = []
    ): Generator {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(PDO::FETCH_UNIQUE)) {
            $key = array_shift($row);
            yield $key => $row;
        }
    }
}
