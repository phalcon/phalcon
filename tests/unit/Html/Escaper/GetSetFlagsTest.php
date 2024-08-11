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

namespace Phalcon\Tests\Unit\Html\Escaper;

use Phalcon\Html\Escaper;
use Phalcon\Tests\AbstractUnitTestCase;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

final class GetSetFlagsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Escaper :: getFlags() / setFlags()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEscaperGetSetFlags(): void
    {
        $escaper = new Escaper();

        $expected = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
        $actual   = $escaper->getFlags();
        $this->assertSame($expected, $actual);

        $expected = 'That&#039;s right';
        $actual   = $escaper->attributes("That's right");
        $this->assertSame($expected, $actual);

        $escaper->setFlags(ENT_HTML401);

        $expected = ENT_HTML401;
        $actual   = $escaper->getFlags();
        $this->assertSame($expected, $actual);

        $escaper->setFlags(ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
        $expected = 'That&#039;s right';
        $actual   = $escaper->attributes("That's right");
        $this->assertSame($expected, $actual);
    }
}
