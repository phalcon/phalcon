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

namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\MetaData\Exceptions\MetaDataDirectoryNotWritable;
use Phalcon\Support\Settings;
use Phalcon\Support\Traits\FilePathTrait;

use function array_key_exists;
use function file_exists;
use function file_put_contents;
use function var_export;

/**
 * Phalcon\Mvc\Model\MetaData\Stream
 *
 * Stores model meta-data in PHP files.
 *
 *```php
 * $metaData = new \Phalcon\Mvc\Model\MetaData\Files(
 *     [
 *         "metaDataDir" => "app/cache/metadata/",
 *     ]
 * );
 *```
 *
 * @phpstan-import-type mvc_metadata_index from MvcTypes
 */
class Stream extends MetaData
{
    use FilePathTrait;

    protected string $metaDataDir = "./";

    /**
     * Phalcon\Mvc\Model\MetaData\Files constructor
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        if (true === array_key_exists("metaDataDir", $options)) {
            /** @var string $metaDataDir */
            $metaDataDir = $options["metaDataDir"];

            $this->metaDataDir = $metaDataDir;
        }
    }

    /**
     * Reads meta-data from files
     *
     * @phpstan-return mvc_metadata_index|null
     */
    public function read(mixed $key): array | null
    {
        if (null === $key) {
            return null;
        }

        /**
         * The interface declares the key as a string.
         *
         * @var string $key
         */
        $path = $this->getFilePath($key);
        if (false === file_exists($path)) {
            return null;
        }
        /** @var mvc_metadata_index */
        return require $path;
    }

    /**
     * Writes the meta-data to files
     *
     * @throws Exception
     *
     * @phpstan-param mvc_metadata_index $data
     */
    public function write(mixed $key, array $data): void
    {
        /**
         * The setting holds a boolean flag. The interface declares the key as
         * a string.
         *
         * @var bool   $option
         * @var string $key
         */
        $option = Settings::get('orm.exception_on_failed_metadata_save');
        try {
            $path = $this->getFilePath($key);

            if (
                false === file_put_contents($path, "<?php return " . var_export($data, true) . "; ")
            ) {
                $this->throwWriteException($option);
            }
        } catch (\Exception) {
            $this->throwWriteException($option);
        }
    }

    /**
     * Builds the cache file path. Namespace separators become "_", so a
     * name that itself contains "_" gets a hash suffix; otherwise "A\\B"
     * and "A_B" would share one file.
     */
    private function getFilePath(string $key): string
    {
        $name = $this->prepareVirtualPath($key);

        if (str_contains($key, '_')) {
            $name .= '_' . sha1($key);
        }

        return $this->metaDataDir . $name . '.php';
    }

    /**
     * Throws an exception when the metadata cannot be written
     */
    private function throwWriteException(mixed $option): void
    {
        if ($option) {
            throw new MetaDataDirectoryNotWritable();
        } else {
            trigger_error(
                "Meta-Data directory cannot be written"
            );
        }
    }
}
