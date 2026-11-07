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

namespace Phalcon\Tests\Unit\Mvc\Model\Query\Lang;

use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Query\Lang;
use Phalcon\Phql\Exception as PhqlException;
use Phalcon\Support\Settings;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function array_fill;
use function count;
use function implode;
use function memory_get_usage;
use function str_repeat;

/**
 * Lang::parsePHQL() behaves as cphalcon's (ext/phalcon/mvc/model/query/base.c,
 * phql_parse_phql()): the AST is cached by the exact statement, tagged with
 * an `id` from a counter starting at `orm.unique_cache_id`, and parse errors
 * are Phalcon\Mvc\Model\Exception.
 *
 * The cache lives until Lang::clean() or Query::clean() - for the rest of
 * the process otherwise - so every test uses statements of its own.
 */
final class ParsePHQLTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        Settings::reset();
        parent::tearDown();
    }

    /**
     * Query::clean() drops the parsed statements along with the intermediate
     * representations cached under their ids.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryCleanAlsoForgetsCachedStatements(): void
    {
        $before = Lang::parsePHQL('SELECT * FROM QueryClean')['id'] ?? null;
        Query::clean();
        $after = Lang::parsePHQL('SELECT * FROM QueryClean')['id'] ?? null;

        $this->assertIsInt($before);
        $this->assertGreaterThan($before, $after);
    }

    /**
     * Long-running workers outlive a request; clean() lets them drop the
     * cached statements, which the next parse tags with a new id.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangCleanForgetsCachedStatements(): void
    {
        $before = Lang::parsePHQL('SELECT * FROM LangClean')['id'] ?? null;
        Lang::clean();
        $after = Lang::parsePHQL('SELECT * FROM LangClean')['id'] ?? null;

        $this->assertIsInt($before);
        $this->assertGreaterThan($before, $after);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLBlankStatementHasNoResult(): void
    {
        $this->assertSame([null, null], [Lang::parsePHQL(''), Lang::parsePHQL(' ')]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLCacheCollision(): void
    {
        $phql1 = "SELECT [TagMe\Modules\Identity\Infrastructure\User\Model\Elasticsearch\Users].* "
               . "FROM [TagMe\Modules\Identity\Infrastructure\User\Model\Elasticsearch\Users] "
               . "WHERE _id = 'GcbN9FIoI8IC3LqBIuCu50AC02TpI8ruKOjyzd1fqOCuna' LIMIT :APL0:";

        $phql2 = "SELECT [TagMe\Modules\Identity\Infrastructure\User\Model\Elasticsearch\Users].* "
               . "FROM [TagMe\Modules\Identity\Infrastructure\User\Model\Elasticsearch\Users] "
               . "WHERE _id = 'GcbN9FIoI8IC3LpcIuCu50AC02TpI8ruKOjyzd1fqOCuna' LIMIT :APL0:";

        $ast1 = Lang::parsePHQL($phql1);
        $ast2 = Lang::parsePHQL($phql2);

        $this->assertNotSame($ast1, $ast2);

        // Each query must keep its own `_id`, not the other's cached value.
        $this->assertSame(
            'GcbN9FIoI8IC3LqBIuCu50AC02TpI8ruKOjyzd1fqOCuna',
            $ast1['where']['right']['value']
        );
        $this->assertSame(
            'GcbN9FIoI8IC3LpcIuCu50AC02TpI8ruKOjyzd1fqOCuna',
            $ast2['where']['right']['value']
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLCacheCollisionMinimal(): void
    {
        $ast1 = Lang::parsePHQL("SELECT * FROM Robots WHERE name = 'B0'");
        $ast2 = Lang::parsePHQL("SELECT * FROM Robots WHERE name = 'AQ'");

        $this->assertSame('B0', $ast1['where']['right']['value']);
        $this->assertSame('AQ', $ast2['where']['right']['value']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLCacheCollisionParseOrder(): void
    {
        // Parse the second one first to prove neither order poisons the cache.
        $ast2 = Lang::parsePHQL("SELECT * FROM Robots WHERE name = 'pc'");
        $ast1 = Lang::parsePHQL("SELECT * FROM Robots WHERE name = 'qB'");

        $this->assertSame('pc', $ast2['where']['right']['value']);
        $this->assertSame('qB', $ast1['where']['right']['value']);
    }

    /**
     * Literals are checked while parsing; a statement already in the cache
     * is returned as it was parsed, whatever `orm.enable_literals` is now.
     * cphalcon 5.23.0 with the same steps: the first parse gets an id, the
     * second returns the same AST (same id), the third throws "Literals are
     * disabled in PHQL statements".
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLCacheHitBypassesLiteralCheck(): void
    {
        $cached = Lang::parsePHQL("SELECT * FROM LiteralBypass WHERE a = 'x'");

        Settings::set('orm.enable_literals', false);

        $this->assertSame($cached, Lang::parsePHQL("SELECT * FROM LiteralBypass WHERE a = 'x'"));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        Lang::parsePHQL("SELECT * FROM LiteralBypass WHERE a = 'y'");
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLCacheLevelMinusOneHasNoId(): void
    {
        Settings::set('orm.cache_level', -1);

        $this->assertArrayNotHasKey('id', Lang::parsePHQL('SELECT * FROM CacheLevelMinusOne') ?? []);
    }

    /**
     * Level 0 caches without an id; the cached AST keeps having none.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLCacheLevelZeroCachesWithoutId(): void
    {
        Settings::set('orm.cache_level', 0);
        $first = Lang::parsePHQL('SELECT * FROM CacheLevelZero');

        Settings::set('orm.cache_level', 3);
        $second = Lang::parsePHQL('SELECT * FROM CacheLevelZero');

        $this->assertSame([false, $first], [isset($first['id']), $second]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLCacheReturnsSameAst(): void
    {
        $phql = "SELECT * FROM Robots WHERE name = 'cacheHit'";

        $this->assertSame(Lang::parsePHQL($phql), Lang::parsePHQL($phql));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLErrorsAreModelExceptions(): void
    {
        try {
            Lang::parsePHQL('SELECT * FROM ParseErrors WHERE id = = 1');
            $this->fail('no exception');
        } catch (Exception $ex) {
            $this->assertSame(
                [
                    "Syntax error, unexpected token EQUALS, near to ' 1', "
                    . 'when parsing: SELECT * FROM ParseErrors WHERE id = = 1 (40)',
                    PhqlException::class,
                ],
                [$ex->getMessage(), $ex->getPrevious() !== null ? $ex->getPrevious()::class : null]
            );
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLEscapeSequencesInString(): void
    {
        // PHQL sequence => the characters it stands for
        $expected = [
            '\n'   => "\n",
            '\r'   => "\r",
            '\t'   => "\t",
            '\\\\' => '\\',
            "\\'"  => "'",
            '\"'   => '"',
            '\%'   => '\%',
            '\d'   => '\d',
        ];

        foreach ($expected as $sequence => $value) {
            $ast = Lang::parsePHQL(
                "SELECT * FROM Robots WHERE name = 'a" . $sequence . "b'"
            );

            $this->assertSame(
                'a' . $value . 'b',
                $ast['where']['right']['value'],
                $sequence
            );
        }
    }

    /**
     * Ids come from one counter; a cached statement keeps its id.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-07
     */
    public function testMvcModelQueryLangParsePHQLIdRepeatsOnCacheHit(): void
    {
        $first  = Lang::parsePHQL('SELECT * FROM IdSequenceOne')['id'] ?? null;
        $again  = Lang::parsePHQL('SELECT * FROM IdSequenceOne')['id'] ?? null;
        $second = Lang::parsePHQL('SELECT * FROM IdSequenceTwo')['id'] ?? null;

        $this->assertIsInt($first);
        $this->assertSame([$first, $first + 1], [$again, $second]);
    }

    /**
     * cphalcon's version also requires the 40,000-item list to parse in under
     * 3 s; that limit is sized for C. The pure-PHP parser's speed is pinned
     * by phalcon/phql's growth-ratio tests instead.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLLargeInList(): void
    {
        $count = 40000;
        $phql  = 'SELECT * FROM Robots WHERE id IN ('
            . implode(',', array_fill(0, $count, '1')) . ')';

        $actual = Lang::parsePHQL($phql);

        $this->assertSame($count, count($actual['where']['right'] ?? []));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLRejectedLiteralDoesNotLeak(): void
    {
        Settings::set('orm.enable_literals', false);

        $phql = 'SELECT * FROM Robots WHERE name = "' . str_repeat('x', 1000) . '"';

        $before = memory_get_usage();

        for ($i = 0; $i < 5000; $i++) {
            try {
                Lang::parsePHQL($phql);
            } catch (Exception $ex) {
                // Expected: literals are disabled.
            }
        }

        $delta = memory_get_usage() - $before;

        // 5,000 x 1 KB literals leaked ~5.5 MB in cphalcon before its fix.
        $this->assertLessThan(1048576, $delta);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLTrailingBackslashInString(): void
    {
        foreach (['"abc\\', "'abc\\"] as $literal) {
            $caught = null;

            try {
                Lang::parsePHQL('SELECT * FROM Robots WHERE name = ' . $literal);
            } catch (Exception $ex) {
                $caught = $ex;
            }

            $this->assertNotNull($caught, $literal);
            $this->assertStringContainsString('Scanning error', $caught->getMessage());
        }
    }
}
