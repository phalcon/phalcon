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

namespace Phalcon\Support;

/**
 * This class allows to get the installed version of the framework
 */
class Version
{
    /**
     * The constant referencing the major version. Returns 0
     *
     * ```php
     * echo (new Phalcon\Support\Version())
     *          ->getPart(Phalcon\Support\Version::VERSION_MAJOR);
     * ```
     */
    public const VERSION_MAJOR = 0;

    /**
     * The constant referencing the major version. Returns 1
     *
     * ```php
     * echo (new Phalcon\Support\Version())
     *          ->getPart(Phalcon\Support\Version::VERSION_MEDIUM);
     * ```
     */
    public const VERSION_MEDIUM = 1;

    /**
     * The constant referencing the major version. Returns 2
     *
     * ```php
     * echo (new Phalcon\Support\Version())
     *          ->getPart(Phalcon\Support\Version::VERSION_MINOR);
     * ```
     */
    public const VERSION_MINOR = 2;

    /**
     * The constant referencing the major version. Returns 3
     *
     * ```php
     * echo (new Phalcon\Support\Version())
     *          ->getPart(Phalcon\Support\Version::VERSION_SPECIAL);
     * ```
     */
    public const VERSION_SPECIAL = 3;

    /**
     * The constant referencing the major version. Returns 4
     *
     * ```php
     * echo (new Phalcon\Support\Version())
     *          ->getPart(Phalcon\Support\Version::VERSION_SPECIAL_NUMBER);
     * ```
     */
    public const VERSION_SPECIAL_NUMBER = 4;

    /**
     * Returns the active version (string)
     *
     * ```php
     * echo (new Phalcon\Version())->get();
     * ```
     *
     * @return string
     */
    public function get(): string
    {
        $version = $this->getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        $result = $major . "." . $medium . "." . $minor;
        $suffix = $this->getSpecial($special);

        if ('' !== $suffix) {
            /**
             * A pre-release version should be denoted by appending alpha/beta or RC and
             * a patch version.
             * examples 5.0.0alpha2, 5.0.0beta1, 5.0.0RC3
             */
            $result .= $suffix;

            if (0 !== $specialNumber) {
                $result .= $specialNumber;
            }
        }

        return $result;
    }

    /**
     * Returns the numeric active version
     *
     * ```php
     * echo (new Phalcon\Version())->getId();
     * ```
     *
     * @return string
     */
    public function getId(): string
    {
        $version = $this->getVersion();

        $major         = $version[self::VERSION_MAJOR];
        $medium        = $version[self::VERSION_MEDIUM];
        $minor         = $version[self::VERSION_MINOR];
        $special       = $version[self::VERSION_SPECIAL];
        $specialNumber = $version[self::VERSION_SPECIAL_NUMBER];

        return $major
            . sprintf("%02s", $medium)
            . sprintf("%02s", $minor)
            . $special
            . $specialNumber;
    }

    /**
     * Returns a specific part of the version. If the wrong parameter is passed
     * it will return the full version
     *
     * ```php
     * echo (new Phalcon\Version())->getPart(Phalcon\Version::VERSION_MAJOR);
     * ```
     *
     * @param int $part
     *
     * @return string
     */
    public function getPart(int $part): string
    {
        $version = $this->getVersion();

        return match ($part) {
            self::VERSION_MAJOR,
            self::VERSION_MEDIUM,
            self::VERSION_MINOR,
            self::VERSION_SPECIAL_NUMBER => (string)$version[$part],
            self::VERSION_SPECIAL        => $this->getSpecial($version[self::VERSION_SPECIAL]),
            default                      => $this->get(),
        };
    }

    /**
     * Translates a number to a special release.
     *
     * @param int $special
     *
     * @return string
     */
    final protected function getSpecial(int $special): string
    {
        $map = [
            1 => "alpha",
            2 => "beta",
            3 => "RC",
        ];

        return $map[$special] ?? "";
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
    protected function getVersion(): array
    {
        return [6, 0, 0, 1, 1];
    }
}
