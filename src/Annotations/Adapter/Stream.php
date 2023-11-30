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

use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Traits\Php\FileTrait;
use RuntimeException;

use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function str_replace;
use function strtolower;
use function unserialize;

/**
 * Stores the parsed annotations in files. This adapter is suitable for
 * production
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
 */
class Stream extends AbstractAdapter
{
    use FileTrait;

    /**
     * @var string
     */
    protected string $annotationsDir = "./";

    /**
     * Constructor
     *
     * @param array $options = [
     *                       'annotationsDir' => 'phalconDir'
     *                       ]
     */
    public function __construct(array $options = [])
    {
        $this->annotationsDir = $options["annotationsDir"] ?? $this->annotationsDir;
    }

    /**
     * Reads parsed annotations from files
     *
     * @param string $key
     *
     * @return Reflection|bool|int
     */
    public function read(string $key): Reflection | bool | int
    {
        /**
         * Paths must be normalized before be used as keys
         */
        $path = $this->getFileName($key);

        if (true !== $this->phpFileExists($path)) {
            return false;
        }

        $contents = $this->phpFileGetContents($path);

        if (true === empty($contents)) {
            return false;
        }

        $warning = false;
        set_error_handler(
            function () use (&$warning) {
                $warning = true;
            },
            E_WARNING
        );

        $contents = unserialize($contents);

        restore_error_handler();

        if (true === $warning) {
            throw new RuntimeException(
                "Cannot read annotation data"
            );
        }

        return $contents;
    }

    /**
     * Writes parsed annotations to files
     *
     * @param string     $key
     * @param Reflection $data
     *
     * @return void
     * @throws Exception
     */
    public function write(string $key, Reflection $data): void
    {
        /**
         * Paths must be normalized before be used as keys
         */
        $path = $this->getFileName($key);
        $code = serialize($data);

        if (false === $this->phpFilePutContents($path, $code)) {
            throw new Exception(
                "Annotations directory cannot be written"
            );
        }
    }

    /**
     * Returns the normalized name
     *
     * @param string $key
     *
     * @return string
     */
    private function getFileName(string $key): string
    {
        return $this->annotationsDir
            . strtolower(str_replace(["\\", "/", ":"], "_", $key))
            . ".php";
    }
}
