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

use Phalcon\Html\Link\Interfaces\LinkInterface;
use Phalcon\Html\Link\Link;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\Link :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkLinkConstruct(): void
    {
        $link = new Link('payment', 'https://dev.phalcon.ld');

        $class = LinkInterface::class;
        $this->assertInstanceOf($class, $link);
    }
}
