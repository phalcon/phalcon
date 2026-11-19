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

namespace Phalcon\Tests\Database\Mvc\Model\Relation;

use Phalcon\Mvc\Model\Relation;
use Phalcon\Tests\AbstractDatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class IsReusableTest extends AbstractDatabaseTestCase
{
    /**
     * isReusable() reflects the reusable option, defaulting to false.
     *
     * @return array<string, array{0: array, 1: bool}>
     */
    public static function getExamples(): array
    {
        return [
            'true'   => [['reusable' => true], true],
            'false'  => [['reusable' => false], false],
            'absent' => [[], false],
        ];
    }

    /**
     * Tests Phalcon\Mvc\Model\Relation :: isReusable()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-22
     */
    #[DataProvider('getExamples')]
    public function testMvcModelRelationIsReusable(array $options, bool $expected): void
    {
        $relation = new Relation(Relation::HAS_MANY, 'RefModel', 'id', 'ref', $options);

        $this->assertSame($expected, $relation->isReusable());
    }

    /**
     * cphalcon returns the option value, phalcon returns a bool.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/869
     */
    public function testMvcModelRelationIsReusableNumericString(): void
    {
        $relation = new Relation(Relation::HAS_MANY, 'RefModel', 'id', 'ref', ['reusable' => '1']);
        $this->assertEquals(true, $relation->isReusable());

        $relation = new Relation(Relation::HAS_MANY, 'RefModel', 'id', 'ref', ['reusable' => '0']);
        $this->assertEquals(false, $relation->isReusable());
    }
}
