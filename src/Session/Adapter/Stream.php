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
use Phalcon\Support\Traits\PhpFileTrait;
use Phalcon\Support\Traits\PhpFunctionTrait;

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
    use PhpFileTrait;
    use PhpFunctionTrait;

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

        if (true === empty($path)) {
            throw new Exception('The session save path cannot be empty');
        }

        if (true !== $this->phpIsWriteable($path)) {
            throw new Exception(
                'The session save path [' . $path . '] is not writable'
            );
        }

        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $file = $this->path . $this->getPrefixedName($sessionId);

        if (true === file_exists($file) && true === is_file($file)) {
            unlink($file);
        }

        return true;
    }

    /**
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        $pattern = $this->path . $this->prefix . "*";
        $time    = time() - $maxlifetime;

        foreach (glob($pattern) as $file) {
            if (
                true === file_exists($file) &&
                true === is_file($file) &&
                filemtime($file) < $time
            ) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Ignore the savePath and use local defined path
     *
     * @param string $savePath
     * @param string $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * @param string $sessionId
     *
     * @return string
     */
    public function read($sessionId): string
    {
        $name = $this->path . $this->getPrefixedName($sessionId);
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
     * @param string $sessionId
     * @param string $data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $name = $this->path . $this->getPrefixedName($sessionId);

        return (
            false !== $this->phpFilePutContents($name, $data, LOCK_EX)
        );
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
        $name = (string) $name;

        return $this->prefix . $name;
    }
}
