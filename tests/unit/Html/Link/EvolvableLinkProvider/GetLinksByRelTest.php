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

namespace Phalcon\Tests\Unit\Html\Link\EvolvableLinkProvider;

use Phalcon\Html\Link\EvolvableLinkProvider;
use Phalcon\Html\Link\Link;
use Phalcon\Tests\UnitTestCase;

final class GetLinksByRelTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\EvolvableLinkProvider :: getLinksByRel()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkProviderGetLinksByRel(): void
    {
        $links = [
            new Link('canonical', 'https://dev.phalcon.ld'),
            new Link('cite-as', 'https://test.phalcon.ld'),
        ];
        $link  = new EvolvableLinkProvider($links);

        $expected = [
            $links[1],
        ];

        $this->assertSame($expected, $link->getLinksByRel('cite-as'));
        $this->assertSame([], $link->getLinksByRel('unknown'));
    }
}
