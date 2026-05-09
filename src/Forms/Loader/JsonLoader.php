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

namespace Phalcon\Forms\Loader;

use Phalcon\Contracts\Forms\Schema;
use Phalcon\Forms\Exception;

use function array_is_list;
use function file_get_contents;
use function is_array;
use function is_file;
use function is_readable;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Supplies form element definitions from a JSON string or file.
 *
 * When $source looks like an existing, readable file path it is read from
 * disk first; otherwise the value is treated as a raw JSON string.
 */
class JsonLoader implements Schema
{
    /**
     * @param string $source JSON string or path to a JSON file
     */
    public function __construct(
        private readonly string $source
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function load(): array
    {
        $json = $this->source;

        if (is_file($json) && is_readable($json)) {
            $json = (string) file_get_contents($json);
        }

        try {
            $definitions = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new Exception('JSON form schema is invalid: ' . $e->getMessage());
        }

        if (!is_array($definitions) || !array_is_list($definitions)) {
            throw new Exception('JSON form schema must decode to an array');
        }

        $loader = new ArrayLoader($definitions);

        return $loader->load();
    }
}
