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

namespace Phalcon\Tests\Unit\Db\Dialect;

use Phalcon\Db\Dialect\Mysql;
use Phalcon\Support\Settings;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Db\Dialect\Fake\FakeDialect;

final class EscapeTest extends AbstractUnitTestCase
{
    /**
     * A dotted name gets the escape character on the two sides of each
     * part.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-10-07
     */
    public function testDbDialectEscapeDottedName(): void
    {
        $dialect = new Mysql();

        $this->assertSame(
            '`robots`.`id`',
            $dialect->escape('robots.id')
        );
        $this->assertSame(
            '`schema`.`robots`.`id`',
            $dialect->escape('schema.robots.id')
        );
        $this->assertSame(
            '"r"."name"',
            $dialect->escape('r.name', '"')
        );
        $this->assertSame(
            '[]a[].[]b[]',
            $dialect->escape('a.b', '[]')
        );
        $this->assertSame(
            '.a.',
            $dialect->escape('.a.', '.')
        );
    }

    /**
     * A dotted name with an escape character, a "*" or an empty part:
     * each part that is not empty and not "*" gets the escape character on
     * the two sides, and an escape character in a part is doubled.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-10-07
     */
    public function testDbDialectEscapeDottedNameLoop(): void
    {
        $dialect = new Mysql();

        $this->assertSame(
            '`robots```.```id`',
            $dialect->escape('`robots`.`id`')
        );
        $this->assertSame(
            '`robots`.*',
            $dialect->escape('robots.*')
        );
        $this->assertSame(
            '`a*b`.`c`',
            $dialect->escape('a*b.c')
        );
        $this->assertSame(
            '`a`..`b`',
            $dialect->escape('a..b')
        );
        $this->assertSame(
            '.`a`',
            $dialect->escape('.a')
        );
        $this->assertSame(
            '`a`.',
            $dialect->escape('a.')
        );
        $this->assertSame(
            '.',
            $dialect->escape('.')
        );
        $this->assertSame(
            '`a``b`.`c`',
            $dialect->escape('a`b.c')
        );
        $this->assertSame(
            '',
            $dialect->escape('.', '.')
        );
    }

    /**
     * A name with no "." gets the escape character on the two sides. "*"
     * does not change.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-10-07
     */
    public function testDbDialectEscapeName(): void
    {
        $dialect = new Mysql();

        $this->assertSame(
            '`robots`',
            $dialect->escape('robots')
        );
        $this->assertSame(
            '*',
            $dialect->escape('*')
        );
        $this->assertSame(
            '`a``b`',
            $dialect->escape('a`b')
        );
    }

    /**
     * escape() and escapeSchema() have the cphalcon parameter names.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/832
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testDbDialectEscapeNamedArguments(): void
    {
        $dialect = new Mysql();

        $this->assertSame(
            '`robots`',
            $dialect->escape(str: 'robots')
        );
        $this->assertSame(
            '`phalcon`',
            $dialect->escapeSchema(str: 'phalcon')
        );
    }

    /**
     * A dialect that does not set an escape character does not escape the
     * name, as in cphalcon. An escape character in the call is used.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/838
     */
    public function testDbDialectEscapeWithoutEscapeChar(): void
    {
        $dialect = new FakeDialect();

        $this->assertSame('robots', $dialect->escape('robots'));
        $this->assertSame('robots.id', $dialect->escape('robots.id'));
        $this->assertSame('"robots"."id"', $dialect->escape('robots.id', '"'));
        $this->assertSame('phalcon', $dialect->escapeSchema('phalcon'));
    }

    /**
     * With "db.escape_identifiers" off, the name does not change.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-10-07
     */
    public function testDbDialectEscapeWithoutEscapeIdentifiers(): void
    {
        $dialect = new Mysql();

        Settings::set('db.escape_identifiers', false);
        $actual = $dialect->escape('robots.id');
        Settings::reset();

        $this->assertSame('robots.id', $actual);
    }
}
