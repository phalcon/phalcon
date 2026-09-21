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

namespace Phalcon\Tests\Unit\Annotations\Annotation;

use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Docblock\Scanner\Opcode;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

/**
 * T_RESOLVED must never take the value of a parser token. Two case labels
 * with one value in Annotation::getExpression() is a silent bug: PHP gives no
 * warning and the first label wins.
 *
 * cphalcon cannot run this test, because it has the C scanner and no Opcode
 * enum. phalcon has both, and it is also the only place where the collision
 * could happen, so the test stays here.
 */
final class TResolvedCollisionTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-09-21
     */
    public function testTResolvedIsNotAParserToken(): void
    {
        foreach (Opcode::cases() as $case) {
            $this->assertNotSame(
                Annotation::T_RESOLVED,
                $case->value,
                'Opcode::' . $case->name . ' has the value of T_RESOLVED'
            );
        }
    }
}
