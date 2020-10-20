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
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_MAJOR
     * );
     * ```
     */
    public const VERSION_MAJOR = 0;

    /**
     * The constant referencing the major version. Returns 1
     *
     * ```php
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_MEDIUM
     * );
     * ```
     */
    public const VERSION_MEDIUM = 1;

    /**
     * The constant referencing the major version. Returns 2
     *
     * ```php
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_MINOR
     * );
     * ```
     */
    public const VERSION_MINOR = 2;

    /**
     * The constant referencing the major version. Returns 3
     *
     * ```php
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_SPECIAL
     * );
     * ```
     */
    public const VERSION_SPECIAL = 3;

    /**
     * The constant referencing the major version. Returns 4
     *
     * ```php
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_SPECIAL_NUMBER
     * );
     * ```
     */
    public const VERSION_SPECIAL_NUMBER = 4;

    /**
     * Returns the active version (string)
     *
     * ```php
     * echo Phalcon\Version::get();
     * ```
     */
    public static function get(): string
    {
        $version = static::getVersion();

        $major         = (string) $version[self::VERSION_MAJOR];
        $medium        = (string) $version[self::VERSION_MEDIUM];
        $minor         = (string) $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        $result  = $major . '.' . $medium . '.' . $minor;
        $suffix  = static::getSpecial($special);

        if ('' !== $suffix) {
            /**
             * A pre-release version should be denoted by appending a hyphen and
             * a series of dot separated identifiers immediately following the
             * patch version.
             */
            $result .= '-' . $suffix;

            if (0 !== $specialNumber) {
                $result .= '.' . (string) $specialNumber;
            }
        }

        return $result;
    }

    /**
     * Returns the numeric active version
     *
     * ```php
     * echo Phalcon\Version::getId();
     * ```
     */
    public static function getId(): string
    {
        $version = static::getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        return $major
            . sprintf('%02s', $medium)
            . sprintf('%02s', $minor)
            . $special
            . $specialNumber;
    }

    /**
     * Returns a specific part of the version. If the wrong parameter is passed
     * it will return the full version
     *
     * ```php
     * echo Phalcon\Version::getPart(
     *     Phalcon\Version::VERSION_MAJOR
     * );
     * ```
     */
    public static function getPart(int $part): string
    {
        $version = static::getVersion();

        $version[self::VERSION_SPECIAL_NUMBER] = static::getSpecial(
            $version[self::VERSION_SPECIAL]
        );

        return $version[$part] ?? static::get();
    }

    /**
     * Translates a number to a special release.
     */
    protected final static function getSpecial(int $special): string
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
     */
    protected static function getVersion(): array
    {
        return [4, 1, 0, 4, 0];
    }
}
