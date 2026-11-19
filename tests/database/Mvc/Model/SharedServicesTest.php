<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Mvc\Model;

use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Models\Invoices;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\Group;

use function serialize;
use function unserialize;

/**
 * The model gets the "modelsManager" and "modelsMetadata" services with
 * getShared(), as in cphalcon. A service that is not shared is thus made
 * once and kept in the container.
 *
 * @issue https://github.com/phalcon/phalcon/issues/839
 */
#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class SharedServicesTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    private int $managers = 0;

    private int $metaData = 0;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $this->managers = 0;
        $this->metaData = 0;

        $managers = &$this->managers;
        $this->container->set(
            'modelsManager',
            function () use (&$managers) {
                $managers++;

                return new Manager();
            }
        );

        $metaData = &$this->metaData;
        $this->container->set(
            'modelsMetadata',
            function () use (&$metaData) {
                $metaData++;

                return new Memory();
            }
        );
    }

    /**
     * Tests Phalcon\Mvc\Model :: __construct() - not shared manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelConstructUsesTheSharedManager(): void
    {
        $first  = new Invoices();
        $second = new Invoices();

        $this->assertSame($first->getModelsManager(), $second->getModelsManager());
        $this->assertSame(1, $this->managers);
    }

    /**
     * Tests Phalcon\Mvc\Model :: count() - not shared manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelCountUsesTheSharedManager(): void
    {
        Invoices::count();
        Invoices::count();

        $this->assertSame(1, $this->managers);
    }

    /**
     * Tests Phalcon\Mvc\Model :: find() - not shared manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelFindUsesTheSharedManager(): void
    {
        Invoices::find();
        Invoices::findFirst();

        $this->assertSame(1, $this->managers);
    }

    /**
     * Tests Phalcon\Mvc\Model :: getModelsMetaData() - not shared metadata
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelGetModelsMetaDataUsesTheSharedService(): void
    {
        $first  = new Invoices();
        $second = new Invoices();

        $this->assertSame($first->getModelsMetaData(), $second->getModelsMetaData());
        $this->assertSame(1, $this->metaData);
    }

    /**
     * Tests Phalcon\Mvc\Model :: unserialize() - not shared manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelUnserializeLegacyUsesTheSharedManager(): void
    {
        $model = new Invoices();
        $data  = (string) $model->serialize();

        $first = new Invoices();
        $first->unserialize($data);

        $second = new Invoices();
        $second->unserialize($data);

        $this->assertSame($model->getModelsManager(), $first->getModelsManager());
        $this->assertSame($first->getModelsManager(), $second->getModelsManager());
        $this->assertSame(1, $this->managers);
    }

    /**
     * Two unserialized models have the same manager.
     *
     * Tests Phalcon\Mvc\Model :: __unserialize() - not shared manager
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcModelUnserializeUsesTheSharedManager(): void
    {
        $data = serialize(new Invoices());

        /** @var Invoices $first */
        $first = unserialize($data);
        /** @var Invoices $second */
        $second = unserialize($data);

        $this->assertSame($first->getModelsManager(), $second->getModelsManager());
        $this->assertSame(1, $this->managers);
    }
}
