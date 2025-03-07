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

namespace Phalcon\Session\Adapter;

use Phalcon\Session\Exception;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Traits\Php\InfoTrait;
use Phalcon\Traits\Php\IniTrait;

use function error_clear_last;
use function error_get_last;
use function error_reporting;
use function file_exists;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * Phalcon\Session\Adapter\Stream
 *
 * This is the file based adapter. It stores sessions in a file based system
 *
 * ```php
 * <?php
 *
 * use Phalcon\Session\Manager;
 * use Phalcon\Session\Adapter\Stream;
 *
 * $session = new Manager();
 * $files = new Stream(
 *     [
 *         'savePath' => '/tmp',
 *     ]
 * );
 * $session->setAdapter($files);
 * ```
 *
 * @property array  $options
 * @property string $prefix
 * @property string $path
 */
class Stream extends Noop
{
    use FileTrait;
    use InfoTrait;
    use IniTrait;

    /**
     * Session options
     *
     * @var array
     */
    protected array $options = [];

    /**
     * Session prefix
     *
     * @var string
     */
    protected string $prefix = '';

    /**
     * The path of the session files
     *
     * @var string
     */
    private string $path = '';

    /**
     * Stream constructor.
     *
     * @param array $options = [
     *                       'prefix'   => '',
     *                       'savePath' => ''
     *                       ]
     *
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->prefix  = $options['prefix'] ?? '';
        $this->options = $options;

        /**
         * Get the save_path from the passed options. If not defined
         * get it from php.ini
         */
        $path = $options['savePath'] ?? $this->phpIniGet('session.save_path');

        if (empty($path)) {
            throw new Exception('The session save path cannot be empty');
        }

        if (true !== $this->phpIsWritable($path)) {
            throw new Exception(
                'The session save path [' . $path . '] is not writable'
            );
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function destroy(string $id): bool
    {
        $file = $this->path . $this->getPrefixedName($id);

        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }

        return true;
    }

    /**
     * @param int $max_lifetime
     *
     * @return false|int
     * @throws Exception
     */
    public function gc(int $max_lifetime): false | int
    {
        $pattern = $this->path . $this->prefix . "*";
        $time    = time() - $max_lifetime;
        $glob    = $this->getGlobFiles($pattern);

        if (false === $glob) {
            $last = error_get_last();
            throw new Exception($last['message'] ?? 'Unexpected gc error');
        }

        if (!empty($glob)) {
            foreach ($glob as $file) {
                if (
                    file_exists($file) &&
                    is_file($file) &&
                    filemtime($file) < $time
                ) {
                    unlink($file);
                }
            }
        }

        return 1;
    }

    /**
     * Ignore the savePath and use local defined path
     *
     * @param string $path
     * @param string $name
     *
     * @return bool
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function read(string $id): string
    {
        $name = $this->path . $this->getPrefixedName($id);
        $data = "";

        if (true === $this->phpFileExists($name)) {
            $pointer = $this->phpFopen($name, 'r');

            if (true === flock($pointer, LOCK_SH)) {
                $data = $this->phpFileGetContents($name);
            }

            fclose($pointer);

            if (false === $data) {
                return "";
            }
        }

        return $data;
    }

    /**
     * @param string $id
     * @param string $data
     *
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        $name = $this->path . $this->getPrefixedName($id);

        return false !== $this->phpFilePutContents($name, $data, LOCK_EX);
    }

    /**
     * Gets the glob array or returns false on failure
     *
     * @param string $pattern
     *
     * @return array|false
     */
    protected function getGlobFiles(string $pattern): array | false
    {
        $errorLevel = error_reporting(0);
        error_clear_last();
        $glob = glob($pattern);
        error_reporting($errorLevel);

        return $glob;
    }

    /**
     * Helper method to get the name prefixed
     *
     * @param mixed $name
     *
     * @return string
     */
    protected function getPrefixedName($name): string
    {
        $name = (string)$name;

        return $this->prefix . $name;
    }
}
