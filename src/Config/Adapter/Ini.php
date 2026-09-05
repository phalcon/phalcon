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

namespace Phalcon\Config\Adapter;

use Phalcon\Config\Config;
use Phalcon\Config\Exception;
use Phalcon\Config\Exceptions\CannotLoadConfigFile;
use Phalcon\Contracts\Config\ConfigTypes;
use Phalcon\Traits\Php\IniTrait;

use function basename;
use function is_array;
use function is_numeric;
use function preg_match;
use function strpos;
use function strtolower;
use function substr;

use const INI_SCANNER_RAW;

/**
 * Reads ini files and converts them to Phalcon\Config\Config objects.
 *
 * Given the next configuration file:
 *
 *```ini
 * [database]
 * adapter = Mysql
 * host = localhost
 * username = scott
 * password = cheetah
 * dbname = test_db
 *
 * [phalcon]
 * controllersDir = "../app/controllers/"
 * modelsDir = "../app/models/"
 * viewsDir = "../app/views/"
 * ```
 *
 * You can read it as follows:
 *
 *```php
 * use Phalcon\Config\Adapter\Ini;
 *
 * $config = new Ini("path/config.ini");
 *
 * echo $config->phalcon->controllersDir;
 * echo $config->database->username;
 *```
 *
 * PHP constants may also be parsed in the ini file, so if you define a constant
 * as an ini value before calling the constructor, the constant's value will be
 * integrated into the results. To use it this way you must specify the optional
 * second parameter as `INI_SCANNER_NORMAL` when calling the constructor:
 *
 * ```php
 * $config = new \Phalcon\Config\Adapter\Ini(
 *     "path/config-with-constants.ini",
 *     INI_SCANNER_NORMAL
 * );
 * ```
 *
 * @phpstan-import-type config_data from ConfigTypes
 */
class Ini extends Config
{
    use IniTrait;

    /**
     * Ini constructor.
     *
     * @throws Exception
     */
    public function __construct(string $filePath, int $mode = INI_SCANNER_RAW)
    {
        $iniConfig = $this->phpParseIniFile($filePath, true, $mode);

        if (false === $iniConfig) {
            throw new CannotLoadConfigFile(basename($filePath));
        }

        $config = [];

        foreach ($iniConfig as $section => $directives) {
            if (is_array($directives)) {
                $sections = [];

                foreach ($directives as $path => $lastValue) {
                    $sections[] = $this->parseIniString(
                        (string)$path,
                        $lastValue
                    );
                }

                if (!empty($sections)) {
                    $config[$section] = array_replace_recursive(...$sections);
                }

                continue;
            }

            $config[$section] = $this->cast($directives);
        }

        parent::__construct($config);
    }

    /**
     * We have to cast values manually because parse_ini_file() has a poor
     * implementation.
     *
     * Note: this casting is an ini-format compensation and is deliberately
     * specific to this adapter. Ini files carry untyped strings, so
     * `on/yes/true`, `off/no/false`, `null` and numeric strings are decoded
     * here. The json, yaml and php adapters receive natively typed values
     * from their parsers and perform no casting.
     *
     * @return array|float|int|mixed|string|null
     */
    protected function cast(mixed $ini): mixed
    {
        /**
         * `parse_ini_file()` yields strings and nested arrays only; the mixed
         * signature mirrors the untyped Zephir parameter.
         *
         * @var config_data|string $ini
         */
        if (is_array($ini)) {
            return $this->castArray($ini);
        }

        $ini      = (string)$ini;
        $lowerIni = strtolower($ini);

        // Decode null/boolean
        if ('null' === $lowerIni) {
            return null;
        }

        // Decode boolean
        $castMap = [
            'on'    => true,
            'true'  => true,
            'yes'   => true,
            'off'   => false,
            'no'    => false,
            'false' => false,
        ];

        if (false !== isset($castMap[$lowerIni])) {
            return $castMap[$lowerIni];
        }

        // Decode float/int
        if (is_numeric($ini)) {
            if (preg_match('/[.]+/', $ini)) {
                return (float)$ini;
            }

            return (int)$ini;
        }

        return $ini;
    }

    /**
     * @phpstan-param config_data $ini
     *
     * @phpstan-return config_data
     */
    protected function castArray(array $ini): array
    {
        foreach ($ini as $key => $value) {
            $ini[$key] = $this->cast($value);
        }

        return $ini;
    }

    /**
     * Build multidimensional array from string
     *
     * @param mixed $value
     *
     * @phpstan-return config_data
     */
    protected function parseIniString(string $path, $value): array
    {
        $value    = $this->cast($value);
        $position = strpos($path, Config::DEFAULT_PATH_DELIMITER);

        if (false === $position) {
            return [
                $path => $value,
            ];
        }

        $key  = substr($path, 0, $position);
        $path = substr($path, $position + 1);

        return [
            $key => $this->parseIniString($path, $value),
        ];
    }
}
