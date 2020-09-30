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

use JsonException;

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
     * The following options are used if none specified for json_encode
     *
     * JSON_HEX_TAG, JSON_HEX_APOS, JSON_HEX_AMP, JSON_HEX_QUOT,
     * JSON_UNESCAPED_SLASHES, JSON_THROW_ON_ERROR
     *
     * @see https://www.ietf.org/rfc/rfc4627.txt
     *
     * ```php
     * use Phalcon\Support\Json\Encode;
     *
     * $data = [
     *     'one' => 'two',
     *     'three'
     * ];
     *
     * $encoder = new Encode();
     * echo $encoder($data);
     * echo $encoder->__invoke($data);
     * echo (new Encode())($data);
     * // {"one":"two","0":"three"}
     * ```
     *
     * @param mixed $data    JSON data to parse
     * @param int   $options Bitmask of JSON decode options.
     * @param int   $depth   Recursion depth.
     *
     * @return string
     *
     * @throws JsonException if the JSON cannot be encoded.
     * @link http://www.php.net/manual/en/function.json-encode.php
     */
    public function __invoke(
        $data,
        int $options = 4194383,
        int $depth = 512
    ): string {
        $encoded = json_encode($data, $options, $depth);

        /**
         * The above will throw an exception when JSON_THROW_ON_ERROR is
         * specified. If not, the code below will handle the exception when
         * an error occurs
         */
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return (string) $encoded;
    }
}
