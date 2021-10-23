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

use function fclose;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fopen;
use function fwrite;
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
     * @param string $filename
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
     * Closes an open file pointer
     *
     * @link https://php.net/manual/en/function.fclose.php
     *
     * @param resource $handle
     *
     * @return bool
     */
    public function phpFclose($handle)
    {
        return fclose($handle);
    }

    /**
     * Gets line from file pointer and parse for CSV fields
     *
     * @param resource $stream
     * @param int      $length
     * @param string   $separator
     * @param string   $enclosure
     * @param string   $escape
     *
     * @return array|null|false
     *
     * @link https://php.net/manual/en/function.fgetcsv.php
     */
    protected function phpFgetCsv(
        $stream,
        $length = 0,
        $separator = ',',
        $enclosure = '"',
        $escape = '\\'
    ) {
        return fgetcsv($stream, $length, $separator, $enclosure, $escape);
    }


    /**
     * @param string $filename
     * @param string $mode
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
     * Binary-safe file write
     *
     * @link https://php.net/manual/en/function.fwrite.php
     *
     * @param resource $handle
     * @param string   $data
     *
     * @return int|false
     */
    protected function phpFwrite($handle, string $data)
    {
        return fwrite($handle, $data);
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
     * Parse a configuration file
     *
     * @param string $filename
     * @param bool   $process_sections
     * @param int    $scanner_mode
     *
     * @return array|false
     *
     * @link https://php.net/manual/en/function.parse-ini-file.php
     */
    protected function phpParseIniFile(
        $filename,
        $process_sections = false,
        $scanner_mode = 1
    ) {
        return parse_ini_file($filename, $process_sections, $scanner_mode);
    }

    /**
     * @param string $filename
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
