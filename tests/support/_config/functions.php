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

/**
 * Initialize ini values and xdebug if it is loaded
 */
if (!function_exists('loadIni')) {
    function loadIni()
    {
        error_reporting(-1);

        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        setlocale(LC_ALL, 'en_US.utf-8');

        date_default_timezone_set('UTC');

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('utf-8');
        }

        if (function_exists('mb_substitute_character')) {
            mb_substitute_character('none');
        }

        clearstatcache();

        if (extension_loaded('xdebug')) {
            ini_set('xdebug.cli_color', '1');
            ini_set('xdebug.dump_globals', 'On');
            ini_set('xdebug.show_local_vars', 'On');
            ini_set('xdebug.max_nesting_level', '100');
            ini_set('xdebug.var_display_max_depth', '4');
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (defined($key)) {
            return constant($key);
        }

        if (getenv($key) !== false) {
            return getenv($key);
        }

        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('getOptionsBeanstalk')) {
    function getOptionsBeanstalk(): array
    {
        return [
            'host' => env('DATA_BEANSTALKD_HOST'),
            'port' => env('DATA_BEANSTALKD_PORT'),
        ];
    }
}
