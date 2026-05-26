<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Db;

use Phalcon\Db\Exceptions\CheckExpressionRequired;
use Phalcon\Db\Exceptions\InvalidCheckExpression;

/**
 * Allows to define `CHECK` constraints on tables. CHECK constraints enforce
 * a boolean SQL predicate on each row of the table; rows that fail the
 * predicate are rejected at INSERT/UPDATE time.
 *
 *```php
 * use Phalcon\Db\Check;
 *
 * $positivePrice = new Check(
 *     "chk_price_positive",
 *     [
 *         "expression" => "price > 0",
 *     ]
 * );
 *
 * // Used inside a createTable() definition
 * $connection->createTable(
 *     "products",
 *     null,
 *     [
 *         "columns" => [ ... ],
 *         "checks"  => [$positivePrice],
 *     ]
 * );
 *```
 */
class Check implements CheckInterface
{
    /**
     * The boolean SQL predicate this constraint enforces.
     *
     * @var string
     */
    protected string $expression;

    /**
     * The CHECK constraint name. An empty string indicates an unnamed
     * constraint — the dialect will emit the clause without a `CONSTRAINT`
     * prefix in that case.
     *
     * @var string
     */
    protected string $name;

    /**
     * Phalcon\Db\Check constructor.
     *
     * @param string $name
     * @param array  $definition
     *
     * @throws Exception
     */
    public function __construct(string $name, array $definition)
    {
        if (!isset($definition['expression'])) {
            throw new CheckExpressionRequired();
        }

        $expression = $definition['expression'];

        if (!is_string($expression) || $expression === '') {
            throw new InvalidCheckExpression();
        }

        $this->name       = $name;
        $this->expression = $expression;
    }

    /**
     * Returns the CHECK expression
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Returns the constraint name (may be an empty string for unnamed)
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
