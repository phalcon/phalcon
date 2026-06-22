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

/**
 * Phalcon\Support\Settings
 *
 * Provides a PHP-userland layer for reading and overriding the Phalcon
 * framework's settings (orm.*, db.*, form.*).
 *
 * get() checks PHP-level overrides first, then falls back to
 * ini_get("phalcon.<key>") which reads the value configured in php.ini /
 * .htaccess / per-virtualhost (only available when the C extension is loaded).
 *
 * set() stores the value in the PHP-level overrides array only. It does NOT
 * call ini_set(), so the change is confined to this static state and never
 * modifies the underlying ini configuration. This prevents settings changed
 * by one project from leaking into another project sharing the same PHP
 * worker process.
 *
 * reset() clears only the keys that were previously set via set(), restoring
 * those keys to their ini_get() fallback values.
 */
class Settings
{
    /**
     * Hardcoded defaults - mirror of the C extension's compiled-in global
     * defaults. Used as the final fallback when ini_get() returns false
     * (i.e. the Phalcon C extension is not loaded).
     *
     * @var array<string, bool|int>
     */
    private static array $defaults = [
        'db.escape_identifiers'                 => true,
        'db.force_casting'                      => false,
        'form.strict_entity_property_check'     => false,
        'orm.call_setters_on_hydration'         => false,
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
        'orm.resultset_empty_left_join_model'   => true,
        'orm.resultset_prefetch_records'        => 0,
        'orm.update_snapshot_on_save'           => true,
        'orm.virtual_foreign_keys'              => true,
        'orm.dynamic_update'                    => true,
    ];

    /**
     * PHP-level overrides. Keys stored here take priority over ini_get().
     *
     * @var array
     */
    protected static array $overrides = [];

    /**
     * Returns the value of a known setting.
     *
     * Resolution order:
     *   1. PHP-level override (set via Settings::set())
     *   2. ini_get("phalcon.<key>") - the ini value, honouring php.ini / .htaccess
     *      (only available when the Phalcon C extension is loaded)
     *   3. Hardcoded default - mirrors the C extension's compiled-in defaults
     *   4. null - for unknown keys
     *
     * @param string $key
     *
     * @return bool|int|null
     */
    public static function get(string $key): bool | int | null
    {
        if (isset(self::$overrides[$key])) {
            return self::$overrides[$key];
        }

        $value = ini_get('phalcon.' . $key);

        if (false !== $value) {
            return match ($key) {
                'orm.resultset_prefetch_records' => (int) $value,
                default                          => (bool) $value,
            };
        }

        return self::$defaults[$key] ?? null;
    }

    /**
     * Overrides a setting at the PHP level.
     *
     * Does NOT call ini_set(), so the ini configuration is not modified and
     * no other project sharing this PHP process is affected.
     *
     * Unknown keys are silently ignored.
     *
     * @param string   $key
     * @param bool|int $value
     *
     * @return void
     */
    public static function set(string $key, bool | int $value): void
    {
        switch ($key) {
            case 'db.escape_identifiers':
            case 'db.force_casting':
            case 'form.strict_entity_property_check':
            case 'orm.call_setters_on_hydration':
            case 'orm.case_insensitive_column_map':
            case 'orm.cast_last_insert_id_to_int':
            case 'orm.cast_on_hydrate':
            case 'orm.column_renaming':
            case 'orm.disable_assign_setters':
            case 'orm.enable_implicit_joins':
            case 'orm.enable_literals':
            case 'orm.events':
            case 'orm.exception_on_failed_save':
            case 'orm.exception_on_failed_metadata_save':
            case 'orm.ignore_unknown_columns':
            case 'orm.late_state_binding':
            case 'orm.not_null_validations':
            case 'orm.resultset_empty_left_join_model':
            case 'orm.resultset_prefetch_records':
            case 'orm.update_snapshot_on_save':
            case 'orm.virtual_foreign_keys':
            case 'orm.dynamic_update':
                self::$overrides[$key] = $value;
                break;

            default:
                break;
        }
    }

    /**
     * Clears all PHP-level overrides, restoring get() to return ini_get()
     * fallback values (as configured in php.ini or .htaccess).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$overrides = [];
    }
}
