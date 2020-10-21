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

namespace Phalcon\Tests\Integration\Session\Manager;

use IntegrationTester;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

/**
 * Class ExistsDestroyCest
 *
 * @package Phalcon\Tests\Integration\Session\Manager
 */
class ExistsDestroyCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: exists()/destroy()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionManagerExistsDestroy(IntegrationTester $I)
    {
        $I->wantToTest('Session\Manager - exists()/destroy()');
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $actual = $manager->start();
        $I->assertTrue($actual);

        $actual = $manager->exists();
        $I->assertTrue($actual);

        $manager->destroy();

        $actual = $manager->exists();
        $I->assertFalse($actual);

        $_SESSION = $store;
    }

    /**
     * Tests Phalcon\Session\Manager :: destroy() - clean $_SESSION
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/12326
     * @issue  https://github.com/phalcon/cphalcon/issues/12835
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionManagerDestroySuperGlobal(IntegrationTester $I)
    {
        $I->wantToTest('Session\Manager - destroy() - clean $_SESSION');
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $actual = $manager->start();
        $I->assertTrue($actual);

        $actual = $manager->exists();
        $I->assertTrue($actual);

        $manager->set('test1', __METHOD__);
        $I->assertArrayHasKey('test1', $_SESSION);
        $I->assertEquals(__METHOD__, $_SESSION['test1']);

        $manager->destroy();
        $I->assertArrayNotHasKey('test1', $_SESSION);

        $actual = $manager->exists();
        $I->assertFalse($actual);

        $_SESSION = $store;
    }

    /**
     * Tests Phalcon\Session\Manager :: destroy() - clean $_SESSION with uniquid
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionManagerDestroySuperGlobalUniquid(IntegrationTester $I)
    {
        $I->wantToTest('Session\Manager - destroy() - clean $_SESSION with uniquid');

        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);
        $manager->setOptions(
            [
                'uniqueId' => 'aaa',
            ]
        );

        $actual = $manager->start();
        $I->assertTrue($actual);

        $actual = $manager->exists();
        $I->assertTrue($actual);

        $manager->set('test1', __METHOD__);

        $I->assertArrayHasKey('aaa#test1', $_SESSION);
        $I->assertEquals(__METHOD__, $_SESSION['aaa#test1']);

        $manager->destroy();
        $I->assertArrayNotHasKey('aaa#test1', $_SESSION);

        $actual = $manager->exists();
        $I->assertFalse($actual);

        $_SESSION = $store;
    }
}
