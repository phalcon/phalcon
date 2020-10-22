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

namespace Phalcon\Support\Traits;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fopen;
use function is_writable;
use function unlink;

/**
 * Trait PhpFileTrait
 *
 * @package Phalcon\Support\Traits
 */
trait PhpFileTrait
{
    /**
     * @param string   $filename
     * @param string   $mode
     *
     * @return resource|false
     *
     * @link https://php.net/manual/en/function.fopen.php
     */
    protected function phpFopen($filename, $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * @param string $filename
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.file-exists.php
     */
    protected function phpFileExists($filename)
    {
        return file_exists($filename);
    }

    /**
     * @param string   $filename
     *
     * @return string|false
     *
     * @link https://php.net/manual/en/function.file-get-contents.php
     */
    protected function phpFileGetContents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * @param string   $filename
     * @param mixed    $data
     * @param int      $flags
     * @param resource $context
     *
     * @return int|false
     *
     * @link https://php.net/manual/en/function.file-put-contents.php
     */
    protected function phpFilePutContents(
        $filename,
        $data,
        $flags = 0,
        $context = null
    ) {
        return file_put_contents($filename, $data, $flags, $context);
    }

    /**
     * Gets the value of a configuration option
     *
     * @param string $varname
     *
     * @return string
     *
     * @link https://php.net/manual/en/function.ini-get.php
     * @link https://php.net/manual/en/ini.list.php
     */
    protected function phpIniGet($varname): string
    {
        return ini_get($varname);
    }

    /**
     * Tells whether the filename is writable
     *
     * @param string $filename
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.is-writable.php
     */
    protected function phpIsWriteable($filename): bool
    {
        return is_writable($filename);
    }

    /**
     * @param string   $filename
     *
     * @return bool
     *
     * @link https://php.net/manual/en/function.unlink.php
     */
    protected function phpUnlink($filename)
    {
        return unlink($filename);
    }
}
