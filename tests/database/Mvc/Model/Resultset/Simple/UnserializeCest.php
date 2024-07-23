<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Mvc\Model\Resultset\Simple;

use DatabaseTester;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Storage\Exception;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;

class UnserializeCest
{
    use DiTrait;
    use RecordsTrait;

    /**
     * Executed before each test
     *
     * @param  DatabaseTester $I
     * @return void
     */
    public function _before(DatabaseTester $I): void
    {
        try {
            $this->setNewFactoryDefault();
            $this->setDiService('phpSerializer');
        } catch (Exception $e) {
            $I->fail($e->getMessage());
        }
    }

    /**
     * Tests Phalcon\Mvc\Model\Resultset\Simple :: unserialize()
     *
     * @param  DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-06
     *
     * @group  common
     */
    public function mvcModelResultsetSimpleUnserialize(DatabaseTester $I)
    {
        $I->wantToTest('Mvc\Model\Resultset\Simple - unserialize()');

        $simple = new Simple(null, null, null);

        $expected = [
            'model'       => null,
            'cache'       => null,
            'rows'        => [],
            'columnMap' => [ ['__FAKE__'] ],
            'hydrateMode' => 0
        ];

        $I->assertTrue($this->container->has('serializer'));

        $simple->unserialize(serialize($expected));

        $I->assertEquals([ ['__FAKE__'] ], $I->getProtectedProperty($simple, 'columnMap'));
        $I->assertEquals(0, $I->getProtectedProperty($simple, 'hydrateMode'));
    }
}
