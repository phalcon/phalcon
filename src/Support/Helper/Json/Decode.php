<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Json;

use InvalidArgumentException;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

/**
 * Decodes a string using `json_decode` and throws an exception if the
 * JSON data cannot be decoded
 */
class Decode
{
    /**
     * @param string $data        JSON data to parse
     * @param bool   $associative When `true`, objects are converted to arrays
     * @param int    $depth       Recursion depth.
     * @param int    $options     Bitmask of JSON decode options.
     *
     * @return mixed
     *
     * @throws InvalidArgumentException if the JSON cannot be decoded.
     * @link http://www.php.net/manual/en/function.json-decode.php
     */
    public function __invoke(
        string $data,
        bool $associative = false,
        int $depth = 512,
        int $options = 0
    ) {
        $decoded = json_decode($data, $associative, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $decoded;
    }
}
