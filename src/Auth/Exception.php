<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth;

use Throwable;

/**
 * Phalcon\Auth\Exception
 *
 * Exceptions thrown in Phalcon\Auth will use this class
 */
class Exception extends \Exception
{
    public static function accessDenied(string $type, string $name): self
    {
        return new Exception(
            "Access denied for " . $type . " '" . $name . "'"
        );
    }

    public static function dataMustContainIdKey(): self
    {
        return new Exception(
            "AuthUser data must contain a scalar 'id' key (int|string)"
        );
    }

    public static function configRequiresNonEmptyValue(
        string $configName,
        string $configKey,
        string $suffix = ''
    ): self {
        return new Exception(
            $configName . " requires a non-empty '"
            . $configKey . "'" . $suffix
        );
    }
    public static function doesNotImplement(string $type, string $name): self
    {
        return new Exception(
            $type . " does not implement '" . $name . "'"
        );
    }

    public static function streamFileDoesNotExist(string $path): self
    {
        return new Exception('Stream adapter file does not exist: ' . $path);
    }

    public static function streamFileCannotRead(string $path): self
    {
        return new Exception('Stream adapter cannot read file: ' . $path);
    }

    public static function streamFileDoesNotContainJson(string $path): self
    {
        return new Exception(
            'Stream adapter file does not contain a JSON array: ' . $path
        );
    }

    public static function streamFileNotValidJson(string $path, Throwable $ex): self
    {
        return new Exception(
            'Stream adapter file is not valid JSON: ' . $path,
            0,
            $ex
        );
    }
}
