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

namespace Phalcon\Tests\Database\Mvc\Model\MetaData;

use Codeception\Example;
use DatabaseTester;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

/**
 * Class GetAttributesCest
 */
class GetAttributesCest
{
    use DiTrait;

    public function _before(DatabaseTester $I)
    {
        $this->setNewFactoryDefault();
        $this->setDatabase($I);
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getAttributes()
     *
     * @dataProvider getExamples
     *
     * @param DatabaseTester $I
     * @param Example        $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-02-01
     *
     * @group        mysql
     * @group        pgsql
     * @group        sqlite
     */
    public function mvcModelMetadataGetAttributes(
        DatabaseTester $I,
        Example $example
    ) {
        $I->wantToTest('Mvc\Model\MetaData - getAttributes()');

        $service = $example['service'];

        $adapter    = $this->newService($service);
        $connection = $I->getConnection();
        $adapter->setDi($this->container);

        $adapter->reset();

        $this->container->setShared('modelsMetadata', $adapter);

        /** @var MetaData $metadata */
        $metadata = $this->container->get('modelsMetadata');

        $model    = new Invoices();
        $expected = [
            'inv_id',
            'inv_cst_id',
            'inv_status_flag',
            'inv_title',
            'inv_total',
            'inv_created_at',
        ];
        $actual   = $metadata->getAttributes($model);
        $I->assertEquals($expected, $actual);

        $adapter = $this->newService($service);
        $this->container->setShared('modelsMetadata', $adapter);
        $adapter->setDi($this->container);

        $I->assertNotEquals($adapter, $metadata);

        $actual = $adapter->getAttributes($model);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        return [
            [
                'service'   => 'metadataMemory',
                'className' => 'Memory',
            ],
            [
                'service'   => 'metadataApcu',
                'className' => 'Apcu',
            ],
            [
                'service'   => 'metadataRedis',
                'className' => 'Redis',
            ],
            [
                'service'   => 'metadataLibmemcached',
                'className' => 'Libmemcached',
            ],
        ];
    }
}
