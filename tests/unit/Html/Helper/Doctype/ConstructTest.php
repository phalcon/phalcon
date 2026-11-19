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

namespace Phalcon\Tests\Unit\Html\Helper\Doctype;

use Phalcon\Html\Helper\Doctype;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use const PHP_EOL;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * A subclass can call the parent constructor, as in cphalcon.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/833
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testHtmlHelperDoctypeConstructFromSubclass(): void
    {
        $helper = new class () extends Doctype {
            public function __construct()
            {
                parent::__construct();
            }
        };

        $expected = "<!DOCTYPE html>" . PHP_EOL;
        $actual   = (string) $helper;
        $this->assertSame($expected, $actual);
    }
}
