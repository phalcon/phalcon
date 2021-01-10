<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\Model\MetaData;

use Phiz\Mvc\Model\MetaData;
use Phiz\Mvc\Model\Exception;

/**
 * Phiz\Mvc\Model\MetaData\Stream
 *
 * Stores model meta-data in PHP files.
 *
 *```php
 * $metaData = new \Phiz\Mvc\Model\MetaData\Files(
 *     [
 *         "metaDataDir" => "app/cache/metadata/",
 *     ]
 * );
 *```
 */
class Stream extends MetaData
{
    protected $metaDataDir = "./";

    /**
     * Phiz\Mvc\Model\MetaData\Files constructor
     *
     * @param array options
     */
    public function __construct($options = null)
    {
        $metaDataDir = $options["metaDataDir"] ?? null;
        if ($metaDataDir !== null) {
            $this->metaDataDir = $metaDataDir;
        }
    }

    /**
     * Reads meta-data from files
     */
    public function read(string $key) : array | null
    { 
        $path = $this->metaDataDir . prepare_virtual_path($key, "_") . ".php";

        if (!file_exists($path)) {
            return null;
        }

        return require $path;
    }

    /**
     * Writes the meta-data to files
     */
    public function write(string $key, array $data) : void
    {
        try {
            $path   = $this->metaDataDir . prepare_virtual_path($key, "_") . ".php";
            $option = \globals_get("orm.exception_on_failed_metadata_save");

            if (false === file_put_contents($path, "<?php return " . var_export($data, true) . "; ")) {
                $this->throwWriteException($option);
            }
        } catch (\Exception) {
            $this->throwWriteException($option);
        }
    }

    /**
     * Throws an exception when the metadata cannot be written
     */
    private function throwWriteException($option) : void
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
