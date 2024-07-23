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

namespace Phalcon\Tests\Database\Mvc\Model\MetaData;

use Codeception\Example;
use DatabaseTester;
use Phalcon\Mvc\Model\Exception as ExpectedException;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Storage\Exception;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

class GetSetDICest
{
    use DiTrait;

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
        } catch (Exception $e) {
            $I->fail($e->getMessage());
        }
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getDI() / setDI()
     *
     * @dataProvider getExamples
     *
     * @param DatabaseTester $I
     * @param Example $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function mvcModelMetadataGetSetDI(
        DatabaseTester $I,
        Example $example
    ) {
        $I->wantToTest('Mvc\Model\MetaData - getDI() / setDI()');

        $service = $example['service'];

        $metadata = $this->newService($service);
        $metadata->setDi($this->container);

        $I->assertEquals($this->container, $metadata->getDI());
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getDI() - exception
     *
     * @param  DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-05
     *
     * @group  common
     */
    public function mvcModelMetadataGetDIThrowsException(DatabaseTester $I)
    {
        $I->wantToTest('Mvc\Model\MetaData - getDI() - exception');

        $I->expectThrowable(
            new ExpectedException(
                'A dependency injection container is required to access internal services'
            ),
            function () {
                (new Memory())->getDI();
            }
        );
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'service' => 'metadataMemory',
                'className' => 'Memory',
            ],
            [
                'service' => 'metadataApcu',
                'className' => 'Apcu',
            ],
            [
                'service' => 'metadataRedis',
                'className' => 'Redis',
            ],
            [
                'service' => 'metadataLibmemcached',
                'className' => 'Libmemcached',
            ],
        ];
    }
}
