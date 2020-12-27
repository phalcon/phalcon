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

namespace Phalcon\Tests\Unit\Application;

use Phalcon\Application\AbstractApplication;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Application\ApplicationFixture;
use UnitTester;

use function spl_object_hash;

/**
 * Class GetSetDefaultModuleCest
 *
 * @package Phalcon\Tests\Unit\Application
 */
class GetSetDefaultModuleCest
{
    /**
     * Tests Phalcon\Acl\Role :: getDefaultModule()/setDefaultModule()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function applicationGetSetDefaultModule(UnitTester $I)
    {
        $I->wantToTest('Application - getDefaultModule()/setDefaultModule()');

        $application = new ApplicationFixture();

        $actual = $application->getDefaultModule();
        $I->assertNull($actual);

        $application->setDefaultModule('admin');
        $expected = 'admin';
        $actual   = $application->getDefaultModule();
        $I->assertEquals($expected, $actual);
    }
}
