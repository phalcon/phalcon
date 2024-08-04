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

namespace Phalcon\Tests\Unit\Assets\Manager;

use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\UnitTestCase;

final class HasTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: has()
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-03-16
     */
    public function testAssetsManagerHas(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addCss('/css/style1.css');
        $manager->addCss('/css/style2.css');

        $this->assertTrue($manager->has('css'));
    }

    /**
     * Tests Phalcon\Assets\Manager :: has() - empty
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-03-16
     */
    public function testAssetsManagerHasEmpty(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $this->assertFalse($manager->has('some-non-existent-collection'));
    }
}
