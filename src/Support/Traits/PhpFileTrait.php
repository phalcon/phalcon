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

use function file_get_contents;
use function fopen;
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
     * @param bool     $use_include_path
     * @param resource $context
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
     * @param string   $filename
     * @param bool     $use_include_path
     * @param resource $context
     * @param int      $offset
     * @param int      $maxlen
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
     * @param resource $context
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
