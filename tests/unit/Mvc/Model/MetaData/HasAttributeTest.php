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

final class HasAttributeTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: hasAttribute()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     *
     * @group common
     */
    public function testMvcModelMetadataHasAttribute(
        string $service
    ): void {
        $adapter = $this->newService($service);
        $adapter->setDi($this->container);
        $connection = self::getConnection();

        $adapter->reset();

        $this->container->setShared('modelsMetadata', $adapter);
        /** @var MetaData $metadata */
        $metadata = $this->container->get('modelsMetadata');

        $model = new Invoices();
        $this->assertTrue($metadata->hasAttribute($model, 'inv_id'));
        $this->assertTrue($metadata->hasAttribute($model, 'inv_cst_id'));
        $this->assertTrue($metadata->hasAttribute($model, 'inv_status_flag'));
        $this->assertTrue($metadata->hasAttribute($model, 'inv_title'));
        $this->assertTrue($metadata->hasAttribute($model, 'inv_total'));
        $this->assertTrue($metadata->hasAttribute($model, 'inv_created_at'));

        $this->assertFalse($metadata->hasAttribute($model, 'unknown'));
        $this->assertFalse($adapter->isEmpty());

        /**
         * Double check it can get from cache systems and not memory
         */
        $adapter = $this->newService($service);
        $this->container->setShared('modelsMetadata', $adapter);
        $adapter->setDi($this->container);

        $this->assertNotEquals($adapter, $metadata);

        $this->assertTrue($adapter->isEmpty());

        $this->assertTrue($adapter->hasAttribute($model, 'inv_id'));
        $this->assertTrue($adapter->hasAttribute($model, 'inv_cst_id'));
        $this->assertTrue($adapter->hasAttribute($model, 'inv_status_flag'));
        $this->assertTrue($adapter->hasAttribute($model, 'inv_title'));
        $this->assertTrue($adapter->hasAttribute($model, 'inv_total'));
        $this->assertTrue($adapter->hasAttribute($model, 'inv_created_at'));

        $this->assertFalse($adapter->hasAttribute($model, 'unknown'));
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
