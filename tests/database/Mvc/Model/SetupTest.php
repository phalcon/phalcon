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

use Phalcon\Mvc\Model;
use Phalcon\Support\Settings;
use Phalcon\Tests\AbstractDatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

use function is_int;

#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class SetupTest extends AbstractDatabaseTestCase
{
    public function tearDown(): void
    {
        Settings::reset();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool|int}>
     */
    public static function nullOptionProvider(): array
    {
        return [
            'events'                        => ['events', 'orm.events', false],
            'virtualForeignKeys'            => ['virtualForeignKeys', 'orm.virtual_foreign_keys', false],
            'columnRenaming'                => ['columnRenaming', 'orm.column_renaming', false],
            'notNullValidations'            => ['notNullValidations', 'orm.not_null_validations', false],
            'exceptionOnFailedSave'         => ['exceptionOnFailedSave', 'orm.exception_on_failed_save', false],
            'exceptionOnFailedMetaDataSave' => [
                'exceptionOnFailedMetaDataSave',
                'orm.exception_on_failed_metadata_save',
                false,
            ],
            'phqlLiterals'                  => ['phqlLiterals', 'orm.enable_literals', false],
            'lateStateBinding'              => ['lateStateBinding', 'orm.late_state_binding', false],
            'castOnHydrate'                 => ['castOnHydrate', 'orm.cast_on_hydrate', false],
            'ignoreUnknownColumns'          => ['ignoreUnknownColumns', 'orm.ignore_unknown_columns', false],
            'caseInsensitiveColumnMap'      => [
                'caseInsensitiveColumnMap',
                'orm.case_insensitive_column_map',
                false,
            ],
            'updateSnapshotOnSave'          => ['updateSnapshotOnSave', 'orm.update_snapshot_on_save', false],
            'disableAssignSetters'          => ['disableAssignSetters', 'orm.disable_assign_setters', false],
            'castLastInsertIdToInt'         => ['castLastInsertIdToInt', 'orm.cast_last_insert_id_to_int', false],
            'prefetchRecords'               => ['prefetchRecords', 'orm.resultset_prefetch_records', 0],
        ];
    }

    /**
     * setup() maps its options onto the global ORM settings.
     *
     * Tests Phalcon\Mvc\Model :: setup()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-06-22
     */
    public function testMvcModelSetup(): void
    {
        Model::setup(['notNullValidations' => false]);
        $this->assertFalse(Settings::get('orm.not_null_validations'));

        Model::setup(['notNullValidations' => true]);
        $this->assertTrue(Settings::get('orm.not_null_validations'));
    }

    /**
     * An option with a null value is a value, as in cphalcon: it gives
     * false, or 0 for "prefetchRecords".
     *
     * Tests Phalcon\Mvc\Model :: setup() - null value
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     *
     * @issue  https://github.com/phalcon/phalcon/issues/837
     */
    #[DataProvider('nullOptionProvider')]
    public function testMvcModelSetupNullOption(
        string $option,
        string $setting,
        bool | int $expected
    ): void {
        Settings::set($setting, is_int($expected) ? 5 : true);

        Model::setup([$option => null]);

        $this->assertSame($expected, Settings::get($setting));
    }
}
