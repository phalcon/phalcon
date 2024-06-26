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

use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Support\Traits\FilePathTrait;
use Phalcon\Support\Traits\IniTrait;

use function array_key_exists;
use function file_exists;
use function file_put_contents;
use function is_null;
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
 */
class Stream extends MetaData
{
    use IniTrait;
    use FilePathTrait;

    /**
     * @var string
     */
    protected $metaDataDir = "./";

    /**
     * Phalcon\Mvc\Model\MetaData\Files constructor
     *
     * @param array options
     */
    public function __construct(array $options = [])
    {
        if (true === array_key_exists("metaDataDir", $options)) {
            $this->metaDataDir = $options["metaDataDir"];
        }
    }

    /**
     * Reads meta-data from files
     */
    public function read(?string $key): array | null
    {
        if (true === is_null($key)) {
            return null;
        }
        $path = $this->metaDataDir . $this->prepareVirtualPath($key) . ".php";
        if (false === file_exists($path)) {
            return null;
        }
        return require $path;
    }

    /**
     * Writes the meta-data to files
     */
    public function write(?string $key, array $data): void
    {
        try {
            $path   = $this->metaDataDir . $this->prepareVirtualPath($key) . ".php";
            $option = $this->iniGetBool("phalcon.orm.exception_on_failed_metadata_save");

            if (false === file_put_contents($path, "<?php return " . var_export($data, true) . "; ")) {
                $this->throwWriteException($option);
            }
        } catch (\Exception $e) {
            $this->throwWriteException($option);
        }
    }

    /**
     * Throws an exception when the metadata cannot be written
     */
    private function throwWriteException($option): void
    {
        if ($option) {
            throw new Exception(
                "Meta-Data directory cannot be written"
            );
        } else {
            trigger_error(
                "Meta-Data directory cannot be written"
            );
        }
    }
}
