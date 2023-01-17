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

namespace Phalcon\Http\Request;

use function defined;
use function finfo_close;
use function finfo_file;
use function finfo_open;
use function is_uploaded_file;
use function move_uploaded_file;
use function pathinfo;

use const FILEINFO_MIME_TYPE;
use const PATHINFO_EXTENSION;

/**
 * Phalcon\Http\Request\File
 *
 * Provides OO wrappers to the $_FILES superglobal
 *
 *```php
 * use Phalcon\Mvc\Controller;
 *
 * class PostsController extends Controller
 * {
 *     public function uploadAction()
 *     {
 *         // Check if the user has uploaded files
 *         if ($this->request->hasFiles() == true) {
 *             // Print the real file names and their sizes
 *             foreach ($this->request->getUploadedFiles() as $file) {
 *                 echo $file->getName(), " ", $file->getSize(), "\n";
 *             }
 *         }
 *     }
 * }
 *```
 */
class File implements FileInterface
{
    /**
     * @var int
     */
    protected int $error = 0;

    /**
     * @var string
     */
    protected string $extension = '';

    /**
     * @var string
     */
    protected string $key = '';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $realType;

    /**
     * @var int
     */
    protected int $size = 0;

    /**
     * @var string
     */
    protected string $tmpName = '';

    /**
     * @var string
     */
    protected string $type = '';

    /**
     * Constructor
     *
     * @param array       $file
     * @param string|null $key
     */
    public function __construct(array $file, string $key = '')
    {
        if (isset($file['name'])) {
            $this->name = $file['name'];

            if (defined('PATHINFO_EXTENSION')) {
                $this->extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            }
        }

        $this->tmpName = $file['tmp_name'] ?? $this->tmpName;
        $this->size    = $file['size'] ?? $this->size;
        $this->type    = $file['type'] ?? $this->type;
        $this->error   = $file['error'] ?? $this->error;

        if (true !== empty($key)) {
            $this->key = $key;
        }
    }

    /**
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the real name of the uploaded file
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the real mime type of the upload file using finfo
     *
     * @return string
     */
    public function getRealType(): string
    {
        if (empty($this->realType)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if (false !== $finfo) {
                $mime = finfo_file($finfo, $this->tmpName);

                finfo_close($finfo);

                $this->realType = $mime;
            }
        }

        return $this->realType;
    }

    /**
     * Returns the file size of the uploaded file
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the temporary name of the uploaded file
     *
     * @return string
     */
    public function getTempName(): string
    {
        return $this->tmpName;
    }

    /**
     * Returns the mime type reported by the browser
     * This mime type is not completely secure, use getRealType() instead
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     *
     * @return bool
     */
    public function isUploadedFile(): bool
    {
        $name = $this->tmpName;

        return true !== empty($name) && is_uploaded_file($name);
    }

    /**
     * Moves the temporary file to a destination within the application
     *
     * @param string $destination
     *
     * @return bool
     */
    public function moveTo(string $destination): bool
    {
        return move_uploaded_file($this->tmpName, $destination);
    }
}
