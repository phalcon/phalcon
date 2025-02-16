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

namespace Phalcon\Tests\Unit\Mvc\Router\Annotations;

use Phalcon\Annotations\AdapterFactory;
use Phalcon\Di\Di;
use Phalcon\Http\Request;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;

final class AddTraceTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Router\Annotations :: addPurge()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcRouterAnnotationsAddPurge(): void
    {
        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('memory');

        $di = new Di();
        $di->setShared(
            'annotations',
            new \Phalcon\Annotations\Annotations($adapter)
        );
        $di->set('request', new Request());

        $router = new Annotations(false);
        $router->setDI($di);
        $router->addResource('\\Phalcon\\Tests\\Controllers\\Annotations', '/annotations');

        $_SERVER['REQUEST_METHOD'] = 'TRACE';

        $router->handle('/annotations');

        $this->assertSame('annotations', $router->getControllerName());

        $this->assertSame('trace', $router->getActionName());
    }
}
