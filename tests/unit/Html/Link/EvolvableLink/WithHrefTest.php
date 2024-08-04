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

namespace Phalcon\Tests\Unit\Html\Link\EvolvableLink;

use Phalcon\Html\Link\EvolvableLink;
use Phalcon\Tests\UnitTestCase;

final class WithHrefTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\Link :: withHref()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkWithHref(): void
    {
        $href = 'https://dev.phalcon.ld';
        $link = new EvolvableLink('payment', $href);

        $this->assertSame($href, $link->getHref());

        $href        = 'https://test.phalcon.ld';
        $newInstance = $link->withHref($href);

        $this->assertNotSame($link, $newInstance);

        $this->assertSame($href, $newInstance->getHref());
    }
}
