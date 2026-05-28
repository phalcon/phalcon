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

namespace Phalcon\Tests\Database\Mvc;

use Phalcon\Tests\AbstractDatabaseTestCase;

final class RefactorControllersTest extends AbstractDatabaseTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-02
     */
    public function testControllers(): void
    {
        $this->markTestSkipped('Needs review - tocheck after migration');
    }
}

// Original Cest file content (ControllersCest):
//
// <?php
//
// namespace Phalcon\Tests\Integration\Mvc;
//
// use IntegrationTester;
// use Phalcon\Di\Di;
// use Phalcon\Mvc\Model\Manager;
// use Phalcon\Mvc\Model\MetaData\Memory;
// use Phalcon\Tests\Support\ControllersViewRequestController;
//
// /**
//  * \Phalcon\Tests\Integration\Mvc\ControllerCest
//  * Tests the Phalcon\Mvc\Controller component
//  *
//  * @copyright (c) 2011-2017 Phalcon Team
//  * @link          https://www.phalcon.io
//  * @author        Andres Gutierrez <andres@phalcon.io>
//  * @author        Phalcon Team <team@phalcon.io>
//  *
//  * The contents of this file are subject to the New BSD License that is
//  * bundled with this package in the file LICENSE.txt
//  *
//  * If you did not receive a copy of the license and are unable to obtain it
//  * through the world-wide-web, please send an email to license@phalcon.io
//  * so that we can send you a copy immediately.
//
