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

namespace Phalcon\Tests\Unit\Mvc\Router\Group;

use Phalcon\Mvc\Router\Group;
use Phalcon\Tests\UnitTestCase;

final class GetSetPrefixTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router\Group :: getPrefix() when nothing is set
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcRouterGroupGetPrefixEmpty(): void
    {
        $group = new Group();

        $expected = null;
        $actual   = $group->getPrefix();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Router\Group :: getPrefix()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-04-17
     */
    public function testMvcRouterGroupGetSetPrefix(): void
    {
        $group = new Group();

        $group->setPrefix('/blog');

        $expected = '/blog';
        $actual   = $group->getPrefix();
        $this->assertSame($expected, $actual);
    }
}
