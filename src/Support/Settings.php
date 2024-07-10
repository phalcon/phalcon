<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support;

class Settings
{
    /**
     * @var array
     */
    protected static array $settings = [
        'db.escape_identifiers'                 => true,
        'db.force_casting'                      => false,
        'form.strict_entity_property_check'     => false,
        'orm.case_insensitive_column_map'       => false,
        'orm.cast_last_insert_id_to_int'        => false,
        'orm.cast_on_hydrate'                   => false,
        'orm.column_renaming'                   => true,
        'orm.disable_assign_setters'            => false,
        'orm.enable_implicit_joins'             => true,
        'orm.enable_literals'                   => true,
        'orm.events'                            => true,
        'orm.exception_on_failed_save'          => false,
        'orm.exception_on_failed_metadata_save' => true,
        'orm.ignore_unknown_columns'            => false,
        'orm.late_state_binding'                => false,
        'orm.not_null_validations'              => true,
        'orm.resultset_prefetch_records'        => 0,
        'orm.update_snapshot_on_save'           => true,
        'orm.virtual_foreign_keys'              => true,
        'orm.dynamic_update'                    => true,
    ];

    /**
     * @param string $key
     *
     * @return bool|int|null
     */
    public static function get(string $key): bool|int|null
    {
        return self::$settings[$key] ?? null;
    }

    /**
     * @param string   $key
     * @param bool|int $value
     *
     * @return void
     */
    public static function set(string $key, bool|int $value): void
    {
        if (true === isset(self::$settings[$key])) {
            self::$settings[$key] = $value;
        }
    }
}
