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

namespace Phalcon\Tests\Unit\Html\Link\Link;

use Phalcon\Html\Link\Link;
use Phalcon\Tests\UnitTestCase;

final class GetHrefTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\Link :: getHref()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkLinkGetHref(): void
    {
        $href = 'https://dev.phalcon.ld';
        $link = new Link('payment', $href);

        $this->assertSame($href, $link->getHref());
    }
}
