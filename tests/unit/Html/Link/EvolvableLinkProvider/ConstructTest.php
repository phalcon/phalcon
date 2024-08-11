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
use Phalcon\Html\Link\Interfaces\EvolvableLinkProviderInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Html\Link\EvolvableLinkProvider :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLinkEvolvableLinkConstruct(): void
    {
        $link = new EvolvableLinkProvider();

        $class = EvolvableLinkProviderInterface::class;
        $this->assertInstanceOf($class, $link);
    }
}
