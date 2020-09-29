<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Json;

use InvalidArgumentException;

use function json_encode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

/**
 * Class Encode
 *
 * @package Phalcon\Support\Json
 */
class Encode
{
    /**
     * Encodes a string using `json_encode` and throws an exception if the
     * JSON data cannot be encoded
     *
     * ```php
     * use Phalcon\Helper\Json;
     *
     * $data = [
     *     'one' => 'two',
     *     'three'
     * ];
     *
     * echo Json::encode($data);
     * // {"one":"two","0":"three"}
     * ```
     *
     * @param mixed $data    JSON data to parse
     * @param int   $options Bitmask of JSON decode options.
     * @param int   $depth   Recursion depth.
     *
     * @return string
     *
     * @throws InvalidArgumentException if the JSON cannot be encoded.
     * @link http://www.php.net/manual/en/function.json-encode.php
     */
    public function __invoke(
        $data,
        int $options = 0,
        int $depth = 512
    ): string {
        $encoded = json_encode($data, $options, $depth);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return (string) $encoded;
    }
}
