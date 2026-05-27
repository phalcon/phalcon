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
use Phalcon\Forms\Exceptions\YamlExtensionRequired;
use Phalcon\Forms\Exceptions\YamlSchemaNotArray;

use function extension_loaded;
use function is_array;
use function is_file;
use function is_readable;
use function yaml_parse;
use function yaml_parse_file;

/**
 * Supplies form element definitions from a YAML string or file.
 *
 * Requires the PHP `yaml` extension (pecl/yaml).
 *
 * When $source is an existing, readable file path the file is parsed
 * directly; otherwise the value is treated as a raw YAML string.
 */
class YamlLoader implements Schema
{
    /**
     * @param string $source YAML string or path to a YAML file
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
        if (!extension_loaded("yaml")) {
            throw new YamlExtensionRequired();
        }

        if (is_file($this->source) && is_readable($this->source)) {
            $definitions = yaml_parse_file($this->source);
        } else {
            $definitions = yaml_parse($this->source);
        }

        if (!is_array($definitions)) {
            throw new YamlSchemaNotArray();
        }

        $loader = new ArrayLoader($definitions);

        return $loader->load();
    }
}
