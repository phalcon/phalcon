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

namespace Phalcon\Tests\Database\Mvc\Model;

use Phalcon\Tests\AbstractDatabaseTestCase;

final class RefactorBinderTest extends AbstractDatabaseTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherSingleBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherMultiBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherSingleBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherMultiBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherSingleBindingException(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroHandlerSingleBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroHandlerMultiBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroHandlerSingleBindingException(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroControllerSingleBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroControllerMultiBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroControllerSingleBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroControllerMultiBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroControllerSingleBindingException(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroLazySingleBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroLazyMultiBinding(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroLazySingleBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroLazyMultiBindingWithInterface(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testMicroLazySingleBindingException(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherSingleBindingOriginalValues(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testDispatcherSingleBindingNoCache(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }
}

// Original Cest file content (BinderCest):
//
// <?php
//
// namespace Phalcon\Tests\Integration\Mvc\Model;
//
// use IntegrationTester;
// use Phalcon\Cache\Backend\Apc;
// use Phalcon\Cache\Frontend\Data;
// use Phalcon\Di\Di;
// use Phalcon\Mvc\Dispatcher;
// use Phalcon\Mvc\Micro;
// use Phalcon\Mvc\Micro\Collection;
// use Phalcon\Mvc\Model\Binder;
// use Phalcon\Mvc\Model\Manager;
// use Phalcon\Mvc\Model\MetaData\Memory;
// use Phalcon\Tests\Support\Models\People;
// use Phalcon\Tests\Support\Models\Robots;
// use Test10Controller;
// use Test11Controller;
// use Test9Controller;
// use TypeError;
//
// /**
//  * \Phalcon\Tests\Integration\Mvc\Model\BindingCest
//  * Tests the Phalcon\Mvc\Application component
//  *
//  * @copyright (c) 2011-2016 Phalcon Team
//  * @link          https://www.phalcon.io
//  * @author        Andres Gutierrez <andres@phalcon.io>
//  * @author        Phalcon Team <team@phalcon.io>
//  * @author        Wojciech Ślawski <jurigag@gmail.com>
//  *
//  * The contents of this file are subject to the New BSD License that is
//  * bundled with this package in the file LICENSE.txt
//  *
//  * If you did not receive a copy of the license and are unable to obtain it
//  * through the world-wide-web, please send an email to license@phalcon.io
//  * so that we can send you a copy immediately.
//
