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

namespace Phalcon\Version;

/**
 * This class allows to get the installed version of the framework
 */
class Version
{
    /**
     * The constant referencing the major version. Returns 0
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_MAJOR
     * );
     * ```
     */
    public const VERSION_MAJOR = 0;

    /**
     * The constant referencing the major version. Returns 1
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_MEDIUM
     * );
     * ```
     */
    public const VERSION_MEDIUM = 1;

    /**
     * The constant referencing the major version. Returns 2
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_MINOR
     * );
     * ```
     */
    public const VERSION_MINOR = 2;

    /**
     * The constant referencing the major version. Returns 3
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_SPECIAL
     * );
     * ```
     */
    public const VERSION_SPECIAL = 3;

    /**
     * The constant referencing the major version. Returns 4
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_SPECIAL_NUMBER
     * );
     * ```
     */
    public const VERSION_SPECIAL_NUMBER = 4;

    /**
     * Returns the active version (string)
     *
     * ```php
     * echo Phalcon\Version\Version::get();
     * ```
     *
     * @return string
     */
    public static function get(): string
    {

        $version = static::getVersion();

        $result  = $version[self::VERSION_MAJOR]
            . '.'
            . $version[self::VERSION_MEDIUM]
            . '.'
            . $version[self::VERSION_MINOR]
        ;
        $suffix  = static::getSpecial($version[self::VERSION_SPECIAL]);

        if ('' !== $suffix) {
            /**
             * A pre-release version should be denoted by appending a hyphen and
             * a series of dot separated identifiers immediately following the
             * patch version.
             */
            $result .= '-' . $suffix;

            if (0 !== $version[self::VERSION_SPECIAL_NUMBER]) {
                $result .= '.' . $version[self::VERSION_SPECIAL_NUMBER];
            }
        }

        return $result;
    }

    /**
     * Returns the numeric active version
     *
     * ```php
     * echo Phalcon\Version\Version::getId();
     * ```
     *
     * @return string
     */
    public static function getId(): string
    {
        $version = static::getVersion();

        return $version[self::VERSION_MAJOR]
            . sprintf("%02s", $version[self::VERSION_MEDIUM])
            . sprintf("%02s", $version[self::VERSION_MINOR])
            . $version[self::VERSION_SPECIAL]
            . $version[self::VERSION_SPECIAL_NUMBER]
        ;
    }

    /**
     * Returns a specific part of the version. If the wrong parameter is passed
     * it will return the full version
     *
     * ```php
     * echo Phalcon\Version\Version::getPart(
     *     Phalcon\Version\Version::VERSION_MAJOR
     * );
     * ```
     *
     * @param int $part
     *
     * @return string
     */
    public static function getPart(int $part): string
    {
        $version = static::getVersion();

        $version[self::VERSION_SPECIAL] = static::getSpecial(
            $version[self::VERSION_SPECIAL]
        );

        return (string) ($version[$part] ?? static::get());
    }

    /**
     * Translates a number to a special release.
     *
     * @param int $special
     *
     * @return string
     */
    final protected static function getSpecial(int $special): string
    {
        $map = [
            1 => 'alpha',
            2 => 'beta',
            3 => 'RC',
        ];

        return $map[$special] ?? '';
    }

    /**
     * Area where the version number is set. The format is as follows:
     * ABBCCDE
     *
     * A - Major version
     * B - Med version (two digits)
     * C - Min version (two digits)
     * D - Special release: 1 = alpha, 2 = beta, 3 = RC, 4 = stable
     * E - Special release version i.e. RC1, Beta2 etc.
     *
     * @return int[]
     */
    protected static function getVersion(): array
    {
        return [5, 0, 0, 1, 0];
    }
}
