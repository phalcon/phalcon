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

final class IsTemplatedTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\Link :: isTemplated()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkIsTemplated(): void
    {
        $href = 'https://dev.phalcon.ld';
        $link = new EvolvableLink('payment', $href);

        $this->assertFalse($link->isTemplated());

        $href = 'https://dev.{domain}.ld';
        $link = new EvolvableLink('payment', $href);

        $this->assertTrue($link->isTemplated());
    }
}
