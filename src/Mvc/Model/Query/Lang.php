<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Mvc\Model\Query;

use Phalcon\Mvc\Model\Exception;
use Phalcon\Phql\Exception as PhqlException;
use Phalcon\Phql\Parser;
use Phalcon\Support\Settings;

/**
 * Phalcon\Mvc\Model\Query\Lang
 *
 * PHQL is implemented as a parser that translates syntax in that of the
 * target RDBMS. It allows Phalcon to offer a unified SQL language to the
 * developer, while internally doing all the work of translating PHQL
 * instructions to the most optimal SQL instructions depending on the RDBMS
 * type associated with a model.
 *
 * Parsing behaves as cphalcon's phql_parse_phql(): with `orm.cache_level`
 * of 0 or more, the AST of each statement is kept, keyed by the exact
 * statement, and returned as it is on the next call; with 1 or more, each new
 * AST also gets a unique `id` (from a counter starting at
 * `orm.unique_cache_id`), which Query uses to cache its intermediate
 * representation.
 *
 * cphalcon frees this cache at the end of every request. Here it lives as
 * long as the PHP process: long-running workers (Swoole, RoadRunner,
 * FrankenPHP) should call clean() - or Query::clean(), which calls it -
 * between requests, or set `orm.cache_level` to -1.
 *
 * ```php
 * use Phalcon\Mvc\Model\Query\Lang;
 *
 * $intermediate = Lang::parsePHQL(
 *     "SELECT r.* FROM Invoices r LIMIT 10"
 * );
 * ```
 */
abstract class Lang
{
    /**
     * Parsed ASTs by statement.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $cache = [];

    /**
     * The next `id`, read from `orm.unique_cache_id` on first use and never
     * reset: an id handed out twice would let two statements share Query's
     * cached intermediate representation.
     */
    private static ?int $nextId = null;

    /**
     * Forgets every cached statement. Ids are never reused: a statement
     * parsed again gets a new one.
     */
    public static function clean(): void
    {
        self::$cache = [];
    }

    /**
     * Parses a PHQL statement returning an intermediate representation (IR),
     * or null for a statement without any token.
     *
     * A cached statement is returned as first parsed, even if
     * `orm.enable_literals` has changed since, as in cphalcon.
     *
     * @return array<string, mixed>|null
     *
     * @throws Exception
     */
    public static function parsePHQL(string $phql): ?array
    {
        $cacheLevel = (int) Settings::get('orm.cache_level');
        if ($cacheLevel >= 0 && isset(self::$cache[$phql])) {
            return self::$cache[$phql];
        }

        try {
            $ast = (new Parser())
                ->setEnableLiterals((bool) Settings::get('orm.enable_literals'))
                ->parse($phql)
            ;
        } catch (PhqlException $ex) {
            throw new Exception($ex->getMessage(), 0, $ex);
        }

        if ($ast === null) {
            return null;
        }

        if ($cacheLevel >= 1) {
            self::$nextId ??= (int) Settings::get('orm.unique_cache_id');
            $ast['id']    = self::$nextId++;
        }

        if ($cacheLevel >= 0) {
            self::$cache[$phql] = $ast;
        }

        return $ast;
    }
}
