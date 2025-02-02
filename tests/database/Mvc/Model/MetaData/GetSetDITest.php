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

use Phalcon\Mvc\Model\Exception as ExpectedException;
use Phalcon\Mvc\Model\MetaData\Adapter\Memory;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class GetSetDITest extends AbstractDatabaseTestCase
{
    use DiTrait;

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

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getDI() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-05
     *
     * @group mysql
     */
    public function testMvcModelMetadataGetDIThrowsException(): void
    {
        $this->expectException(ExpectedException::class);
        $this->expectExceptionMessage(
            'A dependency injection container is required to access internal services'
        );

        (new Memory())->getDI();
    }

    /**
     * Tests Phalcon\Mvc\Model\MetaData :: getDI() / setDI()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelMetadataGetSetDI(
        string $service
    ): void {
        $this->setNewFactoryDefault();

        $metadata = $this->newService($service);
        $metadata->setDi($this->container);

        $this->assertEquals($this->container, $metadata->getDI());
    }
}
