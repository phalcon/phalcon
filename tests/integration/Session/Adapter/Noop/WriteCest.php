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

namespace Phalcon\Tests\Integration\Session\Adapter\Noop;

use IntegrationTester;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

use function uniqid;

/**
 * Class WriteCest
 *
 * @package Phalcon\Tests\Integration\Session\Adapter\Noop
 */
class WriteCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Noop :: write()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionAdapterNoopWrite(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Noop - write()');

        $adapter = $this->newService('sessionNoop');

        $actual = $adapter->write('test1', uniqid());
        $I->assertTrue($actual);
    }
}
