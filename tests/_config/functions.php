<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/*******************************************************************************
 * Directories
 *******************************************************************************/
/**
 * Returns the output logs folder
 */
if (!function_exists('cacheDir')) {
    function cacheDir(string $fileName = ''): string
    {
        return codecept_output_dir() . 'cache/' . $fileName;
    }
}

/**
 * Returns the data folder
 */
if (!function_exists('dataDir')) {
    function dataDir(string $fileName = ''): string
    {
        return codecept_data_dir() . $fileName;
    }
}

/**
 * Returns the output folder
 */
if (!function_exists('outputDir')) {
    function outputDir(string $fileName = ''): string
    {
        return codecept_output_dir() . $fileName;
    }
}

/**
 * Returns the output logs folder
 */
if (!function_exists('logsDir')) {
    function logsDir(string $fileName = ''): string
    {
        return codecept_output_dir() . 'logs/' . $fileName;
    }
}

/*******************************************************************************
 * Utility
 *******************************************************************************/
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (defined($key)) {
            return constant($key);
        }

        return getenv($key) ?: $default;
    }
}

if (!function_exists('defineFromEnv')) {
    function defineFromEnv(string $name)
    {
        if (defined($name)) {
            return;
        }

        define(
            $name,
            env($name)
        );
    }
}

/**
 * Converts ENV variables to defined for tests to work
 */
if (!function_exists('loadDefined')) {
    function loadDefined()
    {
        defineFromEnv('DATA_MYSQL_CHARSET');
        defineFromEnv('DATA_MYSQL_HOST');
        defineFromEnv('DATA_MYSQL_NAME');
        defineFromEnv('DATA_MYSQL_PASS');
        defineFromEnv('DATA_MYSQL_PORT');
        defineFromEnv('DATA_MYSQL_USER');

        if (!defined('PATH_DATA')) {
            define('PATH_DATA', dataDir());
        }

        if (!defined('PATH_OUTPUT')) {
            define('PATH_OUTPUT', outputDir());
        }
    }
}

/*******************************************************************************
 * Options
 *******************************************************************************/
if (!function_exists('getOptionsLibmemcached')) {
    function getOptionsLibmemcached(): array
    {
        return [
            'client'  => [],
            'servers' => [
                [
                    'host'   => env('DATA_MEMCACHED_HOST', '127.0.0.1'),
                    'port'   => env('DATA_MEMCACHED_PORT', 11211),
                    'weight' => env('DATA_MEMCACHED_WEIGHT', 0),
                ],
            ],
        ];
    }
}

if (!function_exists('getOptionsMysql')) {
    /**
     * Get mysql db options
     */
    function getOptionsMysql(): array
    {
        return [
            'host'     => env('DATA_MYSQL_HOST'),
            'username' => env('DATA_MYSQL_USER'),
            'password' => env('DATA_MYSQL_PASS'),
            'dbname'   => env('DATA_MYSQL_NAME'),
            'port'     => env('DATA_MYSQL_PORT'),
            'charset'  => env('DATA_MYSQL_CHARSET'),
        ];
    }
}

if (!function_exists('getOptionsPostgresql')) {
    /**
     * Get postgresql db options
     */
    function getOptionsPostgresql(): array
    {
        return [
            'host'     => env('DATA_POSTGRES_HOST'),
            'username' => env('DATA_POSTGRES_USER'),
            'password' => env('DATA_POSTGRES_PASS'),
            'port'     => env('DATA_POSTGRES_PORT'),
            'dbname'   => env('DATA_POSTGRES_NAME'),
            'schema'   => env('DATA_POSTGRES_SCHEMA'),
        ];
    }
}

if (!function_exists('getOptionsRedis')) {
    function getOptionsRedis(): array
    {
        return [
            'host'  => env('DATA_REDIS_HOST'),
            'port'  => env('DATA_REDIS_PORT'),
            'index' => env('DATA_REDIS_NAME'),
        ];
    }
}

if (!function_exists('getOptionsSqlite')) {
    /**
     * Get sqlite db options
     */
    function getOptionsSqlite(): array
    {
        return [
            'dbname' => codecept_root_dir(env('DATA_SQLITE_NAME')),
        ];
    }
}

if (!function_exists('getOptionsSessionStream')) {
    /**
     * Get Session Stream options
     */
    function getOptionsSessionStream(): array
    {
        if (!is_dir(cacheDir('sessions'))) {
            mkdir(cacheDir('sessions'));
        }

        return [
            'savePath' => cacheDir('sessions'),
        ];
    }
}