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

namespace Phalcon\Tests\Unit\Mvc\Model\MetaData;

use Codeception\Example;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;
use Phalcon\Tests\Models\InvoicesMap;

final class GetColumnMapTest extends DatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getColumnMap()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-02-01
     *
     * @group common
     */
    public function testMvcModelMetadataGetColumnMap(
        string $service
    ): void {
        $adapter = $this->newService($service);
        $adapter->setDi($this->container);
        $connection = $this->getConnection();

        $adapter->reset();

        $this->container->setShared('modelsMetadata', $adapter);

        /** @var MetaData $metadata */
        $metadata = $this->container->get('modelsMetadata');
        $model    = new Invoices();

        $this->assertNull($metadata->getColumnMap($model));

        $model    = new InvoicesMap();
        $expected = [
            'inv_id'          => 'id',
            'inv_cst_id'      => 'cst_id',
            'inv_status_flag' => 'status_flag',
            'inv_title'       => 'title',
            'inv_total'       => 'total',
            'inv_created_at'  => 'created_at',
        ];

        $this->assertEquals($expected, $metadata->getColumnMap($model));

        $this->assertTrue($adapter->isEmpty());

        /**
         * Double check it can get from cache systems and not memory
         */
        $adapter = $this->newService($service);
        $this->container->setShared('modelsMetadata', $adapter);
        $adapter->setDi($this->container);

        $this->assertNotEquals($adapter, $metadata);

        $this->assertTrue($adapter->isEmpty());

        $this->assertEquals($expected, $adapter->getColumnMap($model));
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'metadataMemory',
            ],
            [
                'metadataApcu',
            ],
            [
                'metadataRedis',
            ],
            [
                'metadataLibmemcached',
            ],
        ];
    }
}
