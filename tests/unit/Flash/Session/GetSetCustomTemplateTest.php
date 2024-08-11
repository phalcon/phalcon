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

namespace Phalcon\Tests\Unit\Flash\Session;

use Phalcon\Flash\Session;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetSetCustomTemplateTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');
    }

    /**
     * Tests Phalcon\Flash\Session :: getCustomTemplate()/setCustomTemplate()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testFlashSessionGetSetCustomTemplate(): void
    {
        $session = $this->container->getShared('session');
        $session->start();

        $flash = new Session();
        $flash->setDI($this->container);

        $actual = $flash->getCustomTemplate();
        $this->assertEmpty($actual);

        $template = '<span class="{cssClasses}">{message}</span>';
        $actual   = $flash->setCustomTemplate($template);
        $this->assertInstanceOf(Session::class, $actual);

        $actual = $flash->getCustomTemplate();
        $this->assertSame($template, $actual);

        $session->destroy();
    }
}
