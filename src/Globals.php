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

} // end namespace Phiz

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
    function globals_get(string $key) {
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
    
    
function route_extract_params(string $pattern) : ?array {
    $prevChp = 0;
    $bracketCount = 0;
    $parenthesesCount = 0;
    $foundPattern = 0;
    $intermediate = 0;
    $numberMatches = 0;

    if (strlen($pattern) === 0) {
        return null;
    }

    $cp_underscore = IntlChar::ord('_');
    $cp_dash = IntlChar::ord('-');
    $cp_colon = IntlChar::ord(':');
    $cp_lbt = IntlChar::ord('{');
    $cp_rbt = IntlChar::ord('}');
    $cp_lps = IntlChar::ord('(');
    $cp_rps = IntlChar::ord(')');
    $cp_bsl = IntlChar::ord('\\');

    $matches = [];
    $route = "";
    $notValid = false;
    // provide array of UTF8 characters
    $chars = preg_split('//u', $pattern, null, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $cindex => $ch) {
        $ch_pt = IntlChar::ord($ch);
        if ($parenthesesCount === 0) {
            if ($ch_pt === $cp_lbt) {
                if ($bracketCount === 0) {
                    $marker = $cindex + 1;
                    $intermediate = 0;
                    $notValid = false;
                }
                $bracketCount++;
            } elseif ($ch_pt === $cp_rbt) {
                $bracketCount--;
                if ($intermediate > 0) {
                    if ($bracketCount === 0) {
                        $numberMatches++;
                        $variable = null;
                        $regexp = null;
                        // the substring is an  slice of $chars array
                        $item = array_slice($chars, $marker, $cindex - $marker);
                        $item_str = implode('', $item); // need this later
                        foreach ($item as $cursorVar => $chv) {
                            $cpt = IntlChar::ord($chv);
                            if ($cpt === 0) { // how did \0 get here?
                                break;
                            }
                            if (($cursorVar === 0) && !IntlChar::isalpha($cpt)) {
                                $notValid = true;
                                break;
                            }
                            if (IntlChar::isalnum($cpt) ||
                                    $cpt === $cp_dash || $cpt === $cp_underscore || $cpt === $cp_colon) {
                                if ($cpt === $cp_colon) {
                                    $variable = implode('', array_slice($item, 0, $cursorVar));
                                    $regexp = array_slice($item, $cursorVar + 1);
                                    break;
                                }
                            } else {
                                $notValid = true;
                                break;
                            }
                        }

                        if (!$notValid) {
                            $tmp = $numberMatches;
                            if (!empty($variable) && !empty($regexp)) {
                                $foundPattern = 0;
                                foreach ($regexp as $ch) {
                                    $cpt = IntlChar::ord($ch);
                                    if ($cpt === 0) {
                                        break;
                                    }
                                    if (!$foundPattern) {
                                        if ($cpt === $cp_lps) {
                                            $foundPattern = 1;
                                        }
                                    } else {
                                        if ($cpt === $cp_rps) {
                                            $foundPattern = 2;
                                            break;
                                        }
                                    }
                                }

                                $rxstr = implode('', $regexp);
                                if ($foundPattern != 2) {
                                    $route .= "(" . $rxstr . ")";
                                } else {
                                    $route .= $rxstr;
                                }
                                $matches[$variable] = $tmp;
                            } else {
                                $route .= "([^/]*)";
                                $matches[$item_str] = $tmp;
                            }
                        } else {
                            $route .= "{" . $item_str . "}";
                        }
                        continue;
                    }
                }
            }
        }

        if ($bracketCount === 0) {
            if ($ch_pt === $cp_lps) {
                $parenthesesCount++;
            } elseif ($ch_pt === $cp_rps) {
                $parenthesesCount--;
                if ($parenthesesCount === 0) {
                    $numberMatches++;
                }
            }
        }

        if ($bracketCount > 0) {
            $intermediate++;
        } else {
            if (($parenthesesCount === 0) && ($prevChp !== $cp_bsl)) {
                if (strpos(".+|#", $ch) !== false) {
                    $route .= '\\';
                }
            }
            $route .= $ch;
            $prevChp = $ch_pt;
        }
    }
    return [$route, $matches];
}
    
function prepare_virtual_path(string $path, string $separator) : string
{
    $chars = preg_split('//u', $path, null, PREG_SPLIT_NO_EMPTY);
    $cp_bsl = IntlChar::ord('\\');
    $cp_fsl = IntlChar::ord('/');
    $cp_colon = IntlChar::ord(':');
    $result = "";
    foreach($chars as $ch) {
        $ch_pt = IntlChar::ord($ch);
        if ($ch_pt === $cp_fsl || $ch_pt === $cp_bsl || $ch_pt === $cp_colon) {
            $result .= $separator;
        }
        else {
            $result .= $ch;
        }
    }
    return $result;
}

    /**
     * @return A generator object
     *   $id = $generator->current();
     *   $generator->next();
     * 
     */

    function newIdGenerator() : \Generator {
                $i = 0;
                while(true) {
                    yield $i;
                    $i++;
                }
    }
    
} // end global namespace features



