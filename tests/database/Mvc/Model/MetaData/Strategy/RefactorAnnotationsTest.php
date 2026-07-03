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

namespace Phalcon\Tests\Database\Mvc\Model\MetaData\Strategy;

use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\MetaData\Strategy\Annotations;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Models\Annotations\Robot;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\Group;

final class RefactorAnnotationsTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    public function tearDown(): void
    {
        $this->tearDownDatabase();
    }

    /**
     * The Annotations strategy maps the per-column `#[Column]` flags to the
     * MetaData skip/empty-string indices. The flag arguments use the attribute
     * constructor's names (camelCase): `skipOnInsert`, `skipOnUpdate` and
     * `allowEmptyString`. `column` remaps the property to a different column
     * name (`description` -> `text`), which is the name used in the metadata.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-07-03
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testMvcModelMetadataStrategyAnnotationsColumnFlags(): void
    {
        $strategy = new Annotations();
        $model    = new Robot();

        $metaData = $strategy->getMetaData($model, $this->container);

        $this->assertSame(
            ['deleted' => true],
            $metaData[MetaData::MODELS_AUTOMATIC_DEFAULT_INSERT]
        );

        $this->assertSame(
            ['float' => true, 'longblob' => true],
            $metaData[MetaData::MODELS_AUTOMATIC_DEFAULT_UPDATE]
        );

        $this->assertSame(
            ['name' => 'name', 'text' => 'text'],
            $metaData[MetaData::MODELS_EMPTY_STRING_VALUES]
        );

        $this->assertSame(['id'], $metaData[MetaData::MODELS_PRIMARY_KEY]);
        $this->assertSame('id', $metaData[MetaData::MODELS_IDENTITY_COLUMN]);
    }
}
