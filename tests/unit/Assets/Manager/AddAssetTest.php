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

use Phalcon\Assets\Asset\Css;
use Phalcon\Assets\Manager;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class AddAssetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: addAsset()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testAssetsManagerAddAsset(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addAsset(new Css('/css/style1.css'));
        $this->assertCount(1, $manager->get('css'));
    }

    /**
     * Tests Phalcon\Assets\Manager :: addAsset() - addCss()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testAssetsManagerAddAssetAddCss(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addCss('/css/style2.css');
        $manager->addAsset(new Css('/css/style1.css'));

        $this->assertCount(2, $manager->get('css'));
    }
}
