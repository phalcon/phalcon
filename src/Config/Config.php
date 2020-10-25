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

namespace Phalcon\Config;

use Phalcon\Collection\Collection;

use function array_shift;
use function explode;
use function is_array;
use function is_int;
use function is_object;
use function mb_strtolower;
use function method_exists;

/**
 * `Phalcon\Config` is designed to simplify the access to, and the use of,
 * configuration data within applications. It provides a nested object property
 * based user interface for accessing this configuration data within application
 * code.
 *
 *```php
 * $config = new \Phalcon\Config(
 *     [
 *         "database" => [
 *             "adapter"  => "Mysql",
 *             "host"     => "localhost",
 *             "username" => "scott",
 *             "password" => "cheetah",
 *             "dbname"   => "test_db",
 *         ],
 *         "phalcon" => [
 *             "controllersDir" => "../app/controllers/",
 *             "modelsDir"      => "../app/models/",
 *             "viewsDir"       => "../app/views/",
 *         ],
 *     ]
 * );
 *```
 *
 * @property string|null $pathDelimiter
 */
class Config extends Collection implements ConfigInterface
{
    public const DEFAULT_PATH_DELIMITER = ".";

    /**
     * @var string|null
     */
    protected ?string $pathDelimiter = null;

    /**
     * Gets the default path delimiter
     *
     * @return string
     */
    public function getPathDelimiter(): string
    {
        if (!$this->pathDelimiter) {
            $this->pathDelimiter = self::DEFAULT_PATH_DELIMITER;
        }

        return $this->pathDelimiter;
    }

    /**
     * Merges a configuration into the current one
     *
     *```php
     * $appConfig = new \Phalcon\Config(
     *     [
     *         "database" => [
     *             "host" => "localhost",
     *         ],
     *     ]
     * );
     *
     * $globalConfig->merge($appConfig);
     *```
     *
     * @param mixed $toMerge
     *
     * @return ConfigInterface
     * @throws Exception
     */
    public function merge($toMerge): ConfigInterface
    {
        $source = $this->toArray();

        $this->clear();

        if (false !== is_array($toMerge)) {
            $result = $this->internalMerge(
                $source,
                $toMerge
            );

            $this->init($result);

            return $this;
        }

        if (is_object($toMerge) && $toMerge instanceof ConfigInterface) {
            /**
             * @var ConfigInterface $toMerge
             */
            $result = $this->internalMerge(
                $source,
                $toMerge->toArray()
            );

            $this->init($result);

            return $this;
        }

        throw new Exception('Invalid data type for merge.');
    }

    /**
     * Returns a value from current config using a dot separated path.
     *
     *```php
     * echo $config->path("unknown.path", "default", ".");
     *```
     *
     * @param string     $path
     * @param mixed|null $defaultValue
     * @param mixed|null $delimiter
     *
     * @return mixed
     */
    public function path(string $path, $defaultValue = null, $delimiter = null)
    {
        if (false !== $this->has($path)) {
            return $this->get($path);
        }

        if (false !== empty($delimiter)) {
            $delimiter = $this->getPathDelimiter();
        }

        $config = clone $this;
        $keys   = explode($delimiter, $path);

        while (true !== empty($keys)) {
            $key = array_shift($keys);

            if (true !== $config->has($key)) {
                break;
            }

            if (false !== empty($keys)) {
                return $config->get($key);
            }

            $config = $config->get($key);

            if (false !== empty($config)) {
                break;
            }
        }

        return $defaultValue;
    }

    /**
     * Sets the default path delimiter
     *
     * @param string|null $delimiter
     *
     * @return ConfigInterface
     */
    public function setPathDelimiter(?string $delimiter = null): ConfigInterface
    {
        $this->pathDelimiter = $delimiter;

        return $this;
    }

    /**
     * Converts recursively the object to an array
     *
     *```php
     * print_r(
     *     $config->toArray()
     * );
     *```
     *
     * @return array
     */
    public function toArray(): array
    {
        $results = [];
        $data    = parent::toArray();

        foreach ($data as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }

            $results[$key] = $value;
        }

        return $results;
    }

    /**
     * Performs a merge recursively
     *
     * @param array $source
     * @param array $target
     *
     * @return array
     */
    final protected function internalMerge(array $source, array $target): array
    {
        foreach ($target as $key => $value) {
            if (is_array($value) && isset($source[$key]) && is_array($source[$key])) {
                $source[$key] = $this->internalMerge($source[$key], $value);

                continue;
            }

            if (is_int($key)) {
                $source[] = $value;

                continue;
            }

            $source[$key] = $value;
        }

        return $source;
    }

    /**
     * Sets the collection data
     *
     * @param mixed $element
     * @param mixed $value
     */
    protected function setData($element, $value): void
    {
        $element = (string)$element;
        $key     = ($this->insensitive) ? mb_strtolower($element) : $element;

        $this->lowerKeys[$key] = $element;

        if (false !== is_array($value)) {
            $this->data[$element] = new Config($value);

            return;
        }

        $this->data[$element] = $value;
    }
}
