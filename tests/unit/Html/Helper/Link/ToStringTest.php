<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Html\Helper\Link;

use Phalcon\Html\Escaper;
use Phalcon\Html\Helper\Link;
use Phalcon\Tests\AbstractUnitTestCase;

final class ToStringTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Helper\Link :: __toString() - empty
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testHtmlHelperLinkToStringEmpty(): void
    {
        $escaper = new Escaper();
        $helper  = new Link($escaper);

        $result = $helper();

        $actual = (string)$result;
        $this->assertEmpty($actual);
    }
}
