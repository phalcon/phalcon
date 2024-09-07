<?php

/**
 * $this file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 *
 * Implementation of $this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use PDO;
use PDOStatement;
use Phalcon\DataMapper\Pdo\Connection;

use function implode;

abstract class AbstractStatement
{
    /**
     * @var array
     */
    protected array $store = [];

    /**
     * AbstractQuery constructor.
     *
     * @param Connection $connection
     * @param Bind       $bind
     */
    public function __construct(
        protected Connection $connection,
        protected Bind $bind
    ) {
        $this->store["UNION"] = [];

        $this->reset();
    }

    /**
     * Binds a value inline
     *
     * @param mixed $value
     * @param int   $type
     *
     * @return string
     */
    public function bindInline(mixed $value, int $type = -1): string
    {
        return $this->bind->inline($value, $type);
    }

    /**
     * Binds a value - auto-detects the type if necessary
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $type
     *
     * @return static
     */
    public function bindValue(
        string $key,
        mixed $value,
        int $type = -1
    ): static {
        $this->bind->value($key, $value, $type);

        return $this;
    }

    /**
     * Binds an array of values
     *
     * @param array $values
     *
     * @return static
     */
    public function bindValues(array $values): static
    {
        $this->bind->values($values);

        return $this;
    }

    /**
     * Returns all the bound values
     *
     * @return array
     */
    public function getBindValues(): array
    {
        return $this->bind->getValues();
    }

    /**
     * Return the generated statement
     *
     * @return string
     */
    abstract public function getStatement(): string;

    /**
     * Performs a statement in the connection
     *
     * @return PDOStatement
     */
    public function perform()
    {
        return $this->connection->perform(
            $this->getStatement(),
            $this->getBindValues()
        );
    }

    /**
     * Quotes the identifier
     *
     * @param string $name
     * @param int    $type
     *
     * @return string
     */
    public function quoteIdentifier(
        string $name,
        int $type = PDO::PARAM_STR
    ): string {
        return $this->connection->quote($name, $type);
    }

    /**
     * Resets the internal array
     *
     * @return $this
     */
    public function reset(): static
    {
        $this->store["COLUMNS"]  = [];
        $this->store["FLAGS"]    = [];
        $this->store["FROM"]     = [];
        $this->store["GROUP"]    = [];
        $this->store["HAVING"]   = [];
        $this->store["LIMIT"]    = 0;
        $this->store["ORDER"]    = [];
        $this->store["OFFSET"]   = 0;
        $this->store["PAGE"]     = 0;
        $this->store["PER_PAGE"] = 10;
        $this->store["WHERE"]    = [];
        $this->store["WITH"]     = [];

        return $this;
    }

    /**
     * Indents a collection
     *
     * @param array  $collection
     * @param string $glue
     *
     * @return string
     */
    protected function indent(array $collection, string $glue = ""): string
    {
        if (empty($collection)) {
            return "";
        }

        return " " . implode($glue . " ", $collection);
    }
}
