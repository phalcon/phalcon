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

namespace Phalcon\Annotations\Adapter;

use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Exceptions\AnnotationsDirectoryNotWritable;
use Phalcon\Annotations\Exceptions\CannotReadAnnotationData;
use Phalcon\Annotations\Reflection;
use Phalcon\Contracts\Annotations\AnnotationsTypes;
use Phalcon\Support\Traits\FilePathTrait;
use Phalcon\Traits\Php\FileTrait;

use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function sha1;
use function str_contains;
use function unserialize;

use const E_WARNING;

/**
 * Stores the parsed annotations in files. This adapter is suitable for production
 *
 *```php
 * use Phalcon\Annotations\Adapter\Stream;
 *
 * $annotations = new Stream(
 *     [
 *         "annotationsDir" => "app/cache/annotations/",
 *     ]
 * );
 *```
 *
 * @phpstan-import-type annotations_options from AnnotationsTypes
 */
class Stream extends AbstractAdapter
{
    use FilePathTrait;
    use FileTrait;

    protected string $annotationsDir = "./";

    /**
     * @param array $options = [
     *                       'annotationsDir' => 'phalconDir'
     *                       ]
     *
     * Phalcon\Annotations\Adapter\Stream constructor
     *
     * @phpstan-param annotations_options $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options["annotationsDir"])) {
            /** @var string $annotationsDir */
            $annotationsDir       = $options["annotationsDir"];
            $this->annotationsDir = $annotationsDir;
        }
    }

    /**
     * Reads parsed annotations from files
     *
     * @throws CannotReadAnnotationData
     */
    public function read(string $key): bool | int | Reflection
    {
        /**
         * Paths must be normalized before be used as keys
         */
        $path = $this->getFilePath($key);

        if (!$this->phpFileExists($path)) {
            return false;
        }

        $contents = $this->phpFileGetContents($path);

        if (empty($contents)) {
            return false;
        }

        $warning = false;
        set_error_handler(
            static function () use (&$warning): bool {
                $warning = true;

                return true;
            },
            E_WARNING
        );

        /**
         * Restrict object instantiation to the annotation classes this cache
         * ever stores, so a planted cache file cannot trigger PHP object
         * injection through arbitrary classes (CWE-502).
         */
        $contents = unserialize(
            $contents,
            [
                "allowed_classes" => [
                    Reflection::class,
                    Collection::class,
                    Annotation::class,
                ],
            ]
        );

        restore_error_handler();

        if ($warning) {
            throw new CannotReadAnnotationData();
        }

        /** @var bool|int|Reflection */
        return $contents;
    }

    /**
     * Writes parsed annotations to files
     *
     * @throws AnnotationsDirectoryNotWritable
     */
    public function write(string $key, Reflection $data): void
    {
        /**
         * Paths must be normalized before be used as keys
         */
        $path = $this->getFilePath($key);
        $code = serialize($data);

        if (false === $this->phpFilePutContents($path, $code)) {
            throw new AnnotationsDirectoryNotWritable();
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

        if (str_contains($key, "_")) {
            $name = $name . "_" . sha1($key);
        }

        return $this->annotationsDir . $name . ".php";
    }
}
