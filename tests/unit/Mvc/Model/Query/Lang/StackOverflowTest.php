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
use Phalcon\Mvc\Model\Query\Lang;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function str_repeat;

final class StackOverflowTest extends AbstractUnitTestCase
{
    /**
     * cphalcon's Lemon parser has a 100-entry stack; nesting beyond it fails
     * closed with an exception instead of crashing.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-29
     */
    public function testMvcModelQueryLangParsePHQLStackOverflowFailsClosed(): void
    {
        $depth = 300;
        $phql  = "SELECT * FROM Robots WHERE id = "
            . str_repeat("(", $depth) . "1" . str_repeat(")", $depth);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("nested too deeply");

        Lang::parsePHQL($phql);
    }
}
