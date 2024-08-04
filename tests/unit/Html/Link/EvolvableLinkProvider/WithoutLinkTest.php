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

use function spl_object_hash;

final class WithoutLinkTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\EvolvableLinkProvider :: withoutLink()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkProviderWithoutLink(): void
    {
        $link1    = new Link('canonical', 'https://dev.phalcon.ld');
        $link2    = new Link('cite-as', 'https://test.phalcon.ld');
        $instance = new EvolvableLinkProvider(
            [
                $link1,
                $link2,
            ]
        );

        $newInstance = $instance->withoutLink($link1);

        $this->assertNotSame($instance, $newInstance);

        $expected = [spl_object_hash($link2) => $link2];

        $this->assertSame($expected, $newInstance->getLinks());
    }
}
