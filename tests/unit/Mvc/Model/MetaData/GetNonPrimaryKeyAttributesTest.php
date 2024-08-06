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

namespace Phalcon\Tests\Unit\Mvc\Model\MetaData;

use Codeception\Example;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

final class GetNonPrimaryKeyAttributesTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getNonPrimaryKeyAttributes()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-02-01
     *
     * @group common
     */
    public function testMvcModelMetadataGetNonPrimaryKeyAttributes(
        string $service
    ): void {
        $adapter = $this->newService($service);
        $adapter->setDi($this->container);
        $connection = self::getConnection();

        $adapter->reset();

        $this->container->setShared('modelsMetadata', $adapter);

        /** @var MetaData $metadata */
        $metadata = $this->container->get('modelsMetadata');

        $model    = new Invoices();
        $expected = [
            'inv_cst_id',
            'inv_status_flag',
            'inv_title',
            'inv_total',
            'inv_created_at',
        ];
        $actual   = $metadata->getNonPrimaryKeyAttributes($model);

        $this->assertEquals($expected, $actual);

        $this->assertFalse($adapter->isEmpty());

        /**
         * Double check it can get from cache systems and not memory
         */
        $adapter = $this->newService($service);
        $this->container->setShared('modelsMetadata', $adapter);
        $adapter->setDi($this->container);

        $this->assertNotEquals($adapter, $metadata);

        $this->assertTrue($adapter->isEmpty());
        $actual = $adapter->getNonPrimaryKeyAttributes($model);
        $this->assertEquals($expected, $actual);
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
