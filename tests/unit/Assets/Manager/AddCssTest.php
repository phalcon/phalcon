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
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class AddCssTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Assets\Manager :: addCss()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-13
     */
    #[Test]
    public function testAssetsManagerAddCss(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        $manager->addCss('/css/style1.css');
        $manager->addCss('/css/style2.css');

        $collection = $manager->get('css');

        foreach ($collection as $resource) {
            $this->assertSame('css', $resource->getType());
        }

        $this->assertCount(2, $collection);
    }

    /**
     * Tests Phalcon\Assets\Manager :: addCss() - duplicate
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/10938
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2017-06-02
     */
    #[Test]
    public function testAssetsManagerAddCssDuplicate(): void
    {
        $manager = new Manager(new TagFactory(new Escaper()));

        for ($i = 0; $i < 10; $i++) {
            $manager
                ->addCss('css/style.css')
                ->addJs('script.js')
            ;
        }

        $this->assertCount(1, $manager->getCss());
        $this->assertCount(1, $manager->getJs());

        for ($i = 0; $i < 2; $i++) {
            $manager
                ->addCss('style_' . $i . '.css')
                ->addJs('script_' . $i . '.js')
            ;
        }

        $this->assertCount(3, $manager->getCss());
        $this->assertCount(3, $manager->getJs());
    }
}
