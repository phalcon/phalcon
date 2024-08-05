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

namespace Phalcon\Tests\Unit\Session\Manager;

use Codeception\Example;
use Phalcon\Tests\Fixtures\Traits\SessionTrait;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Session\Manager;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class ExistsDestroyTest extends UnitTestCase
{
    use DiTrait;
    use SessionTrait;

    /**
     * Tests Phalcon\Session\Manager :: exists()/destroy()
     *
     * @dataProvider getClassNames
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSessionManagerExistsDestroy(
        string $name
    ): void {
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService($name);
        $manager->setAdapter($files);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $actual = $manager->exists();
        $this->assertTrue($actual);

        $manager->destroy();

        $actual = $manager->exists();
        $this->assertFalse($actual);

        $_SESSION = $store;
    }

    /**
     * Tests Phalcon\Session\Manager :: destroy() - clean $_SESSION
     *
     * @dataProvider getClassNames
     *
     * @return void
     * @param Example           $example
     *
     * @throws \Phalcon\Storage\Exception
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/12326
     * @issue  https://github.com/phalcon/cphalcon/issues/12835
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testSessionManagerDestroySuperGlobal(
        string $name
    ): void {
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService($name);
        $manager->setAdapter($files);

        $actual = $manager->start();
        $this->assertTrue($actual);

        $actual = $manager->exists();
        $this->assertTrue($actual);

        $manager->set('test1', __METHOD__);
        $this->assertArrayHasKey('test1', $_SESSION);
        $this->assertStringContainsString(__METHOD__, $_SESSION['test1']);

        $manager->destroy();
        $this->assertArrayNotHasKey('test1', $_SESSION);

        $actual = $manager->exists();
        $this->assertFalse($actual);

        $_SESSION = $store;
    }

    /**
     * Tests Phalcon\Session\Manager :: destroy() - clean $_SESSION with uniquid
     *
     * @dataProvider getClassNames
     *
     * @return void
     * @param Example           $example
     *
     * @throws \Phalcon\Storage\Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionManagerDestroySuperGlobalUniquid(
        string $name
    ): void {
        $store    = $_SESSION ?? [];
        $_SESSION = [];

        $manager = new Manager();
        $files   = $this->newService($name);
        $manager->setAdapter($files);
        $manager->setOptions(
            [
                'uniqueId' => 'aaa',
            ]
        );

        $actual = $manager->start();
        $this->assertTrue($actual);

        $actual = $manager->exists();
        $this->assertTrue($actual);

        $manager->set('test1', __METHOD__);

        $this->assertArrayHasKey('aaa#test1', $_SESSION);
        $this->assertStringContainsString(__METHOD__, $_SESSION['aaa#test1']);

        $manager->destroy();
        $this->assertArrayNotHasKey('aaa#test1', $_SESSION);

        $actual = $manager->exists();
        $this->assertFalse($actual);

        $_SESSION = $store;
    }
}
