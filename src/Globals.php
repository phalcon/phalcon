<?php

namespace Phalcon {
    /*
     * Declare global variable and functions
     * which can be accessible at top script level.
     * 
     * Substitutes for Phalcon C-Extension 
     * zephir builtin functions in global namespace,
     * until they are no longer needed.
     * Enforce global namespace dynamic script 
     * globals usage and setup ORM factory defaults.
     * 
     */

    /** call this to setup defaults */
    function getOrmDefaults(): array {
        return [
            "orm.ast_cache" => null,
            "orm.cache_level" => 3,
            "orm.parser_cache" => null,
            "orm.resultset_prefetch_records" => 0,
            "orm.late_state_binding" => true,
            "orm.unique_cache_id" => 3,
            "orm.column_renaming" => true,
            "orm.exception_on_failed_metadata_save" => true,
            "orm.enable_implicit_joins" => true,
            "orm.cast_on_hydrate" => false,
            "orm.disable_assign_setters" => false,
            "orm.case_insensitive_column_map" => false,
            "orm.ignore_unknown_columns" => false,
            "orm.virtual_foreign_keys" => true,
            "orm.events" => true,
            "orm.cast_last_insert_id_to_int" => false,
            "orm.update_snapshot_on_save" => true,
            "orm.not_null_validations" => true,
            "db.force_casting" => true,
            "db.escape_identifiers" => true
        ];
    }

    /**
     * Provide easy call mechanism to include some 
     * old-fashioned programming anachronisms.
     * 
     */
    class Globals {

        static public function init() {
            global $ormGlobals;

            if (!empty($ormGlobals)) {
                return;
            }
            foreach (getOrmDefaults() as $key => $value) {
                $ormGlobals[$key] = $value;
            }
        }

    }

} // end namespace Phalcon

namespace {
    // A script dynamic global
    $ormGlobals = [];

/** These functions only exist for zephir compiled code
 *  and only inside the phalcon extension.
 * ORM used functions for zephir-styled Db and ORM class behavior.
 * 
 * globals_set - specific values affect Db-Model behaviour.
 * @global array $globals
 * @param string $key
 * @param mixed $value
 */
    function globals_set(string $key, mixed $value) {
        global $globals;

        $globals[$key] = $value;
    }

/**
 * globals_get - specific values affect Db-Model behaviour.
 * @global array $ormGlobals
 * @param string $key
 * @return mixed
 */
    function globals_get(string $key): mixed {
        global $ormGlobals;

        return $ormGlobals[$key];
    }

/**
 * Return class path of object in all lower case.
 * 
 */
    function get_class_lower(object $instance): string {
        return strtolower(get_class($instance));
    }

    /**
     * @param object $instance
     * @return string Class name without namespace, as database table name
     */

    function get_class_ns(object $instance): string {
        $full = get_class($instance);
        $ix = strrpos($full, "\\");
        if ($ix === false) {
            return $full;
        }
        return substr($full, $ix + 1);
    }

} // end global namespace features



