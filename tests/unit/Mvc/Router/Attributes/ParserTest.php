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

namespace Phalcon\Tests\Unit\Mvc\Router\Attributes;

use Phalcon\Components\Attributes\AdapterFactory;
use Phalcon\Di\Di;
use Phalcon\Http\Request;
use Phalcon\Mvc\Router\Attributes;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\RouterTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class ParserTest extends AbstractUnitTestCase
{
    use RouterTrait;

    /**
     * Tests Phalcon\Mvc\Router :: general()
     *
     * @author Team Phalcon <team@phalcon.io>
     * @since  2025-02-08
     */
    public function testMvcRouterGeneral(): void
    {
        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('memory');

        $di = new Di();
        $di->setShared(
            'attributes',
            new \Phalcon\Components\Attributes\Attributes($adapter)
        );
        $di->set('request', new Request());

        $router = new Attributes(false);
        $router->setDI($di);
        $router->addResource('\\Phalcon\\Tests\\Controllers\\Attributes', '/attributes');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router->handle('/attributes');

        $this->assertSame(
            'attributes',
            $router->getControllerName()
        );

        $this->assertSame(
            'index',
            $router->getActionName()
        );

        $this->assertSame(
            [],
            $router->getParams()
        );
    }

    /**
     * Tests Phalcon\Mvc\Router :: general()
     *
     * @author Team Phalcon <team@phalcon.io>
     * @since  2025-02-08
     */
    public function testMvcRouterGeneralStream(): void
    {
        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('stream', ['storageDir' => outputDir()]);
        $adapter->clear();

        $di = new Di();
        $di->setShared(
            'attributes',
            new \Phalcon\Components\Attributes\Attributes($adapter)
        );
        $di->set('request', new Request());

        $router = new Attributes(false);
        $router->setDI($di);
        $router->addResource('\\Phalcon\\Tests\\Controllers\\Attributes', '/attributes');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router->handle('/attributes');

        $this->assertSame(
            'attributes',
            $router->getControllerName()
        );

        $this->assertSame(
            'index',
            $router->getActionName()
        );

        $this->assertSame(
            [],
            $router->getParams()
        );
    }

    /**
     * Tests Phalcon\Mvc\Router :: general()
     *
     * @author Team Phalcon <team@phalcon.io>
     * @since  2025-02-08
     * @requires extension apcu
     */
    public function testMvcRouterGeneralApcu(): void
    {
        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('apcu');

        $di = new Di();
        $di->setShared(
            'attributes',
            new \Phalcon\Components\Attributes\Attributes($adapter)
        );
        $di->set('request', new Request());

        $router = new Attributes(false);
        $router->setDI($di);
        $router->addResource('\\Phalcon\\Tests\\Controllers\\Attributes', '/attributes');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $router->handle('/attributes');

        $this->assertSame(
            'attributes',
            $router->getControllerName()
        );

        $this->assertSame(
            'index',
            $router->getActionName()
        );

        $this->assertSame(
            [],
            $router->getParams()
        );
    }

    /**
     * Tests Phalcon\Mvc\Router\Attributes :: delete()
     *
     * @author Team Phalcon <team@phalcon.io>
     * @since  2025-02-08
     */
    public function testMvcRouterDelete(): void
    {
        $factory = new AdapterFactory(new SerializerFactory());
        $adapter = $factory->newInstance('memory');

        $di = new Di();
        $di->setShared(
            'attributes',
            new \Phalcon\Components\Attributes\Attributes($adapter)
        );
        $di->set('request', new Request());

        $router = new Attributes(false);
        $router->setDI($di);
        $router->addResource('\\Phalcon\\Tests\\Controllers\\Attributes', '/attributes');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        $router->handle('/attributes/3');

        $this->assertSame(
            'attributes',
            $router->getControllerName()
        );

        $this->assertSame(
            'delete',
            $router->getActionName()
        );

        $this->assertSame(
            ['id' => 3],
            $router->getParams()
        );
    }
}
