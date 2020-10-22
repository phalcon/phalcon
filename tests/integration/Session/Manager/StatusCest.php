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
 * Class StatusCest
 *
 * @package Phalcon\Tests\Integration\Session\Manager
 */
class StatusCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Manager :: status()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionManagerStatus(IntegrationTester $I)
    {
        $I->wantToTest('Session\Manager - status()');
        $manager = new Manager();
        $files   = $this->newService('sessionStream');
        $manager->setAdapter($files);

        $expected = $manager::SESSION_NONE;
        $actual   = $manager->status();
        $I->assertEquals($expected, $actual);

        $actual = $manager->start();
        $I->assertTrue($actual);

        $expected = $manager::SESSION_ACTIVE;
        $actual   = $manager->status();
        $I->assertEquals($expected, $actual);

        $manager->destroy();

        $expected = $manager::SESSION_NONE;
        $actual   = $manager->status();
        $I->assertEquals($expected, $actual);
    }
}
