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
use Phalcon\Tests\AbstractUnitTestCase;

final class WithRelTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\Link :: withRel()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkWithRel(): void
    {
        $href = 'https://dev.phalcon.ld';
        $link = new EvolvableLink('payment', $href);

        $this->assertSame(['payment'], $link->getRels());

        $newInstance = $link->withRel('citation');

        $this->assertNotSame($link, $newInstance);

        $rels = [
            'payment',
            'citation',
        ];
        $this->assertSame($rels, $newInstance->getRels());
    }
}
