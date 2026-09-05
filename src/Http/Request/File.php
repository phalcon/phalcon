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

use Phalcon\Contracts\Http\HttpTypes;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;

use function defined;
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
 *
 * @phpstan-import-type http_uploaded_file from HttpTypes
 */
class File implements FileInterface
{
    use GetTrait;

    protected int $error        = 0;
    protected string $extension = '';
    protected string $key       = '';
    protected string $name      = '';
    protected string $realType;
    protected int $size       = 0;
    protected string $tmpName = '';
    protected string $type    = '';

    /**
     * Constructor
     *
     * @phpstan-param http_uploaded_file $file
     */
    public function __construct(array $file, string $key = '')
    {
        if (isset($file['name'])) {
            /** @var string $name */
            $name = $file['name'];

            $this->name = $name;

            if (defined('PATHINFO_EXTENSION')) {
                $this->extension = pathinfo($name, PATHINFO_EXTENSION);
            }
        }

        /** @var string $tmpName */
        $tmpName = $this->getArrVal($file, 'tmp_name', $this->tmpName);
        /** @var int $size */
        $size = $this->getArrVal($file, 'size', $this->size);
        /** @var string $type */
        $type = $this->getArrVal($file, 'type', $this->type);
        /** @var int $error */
        $error = $this->getArrVal($file, 'error', $this->error);

        $this->tmpName = $tmpName;
        $this->size    = $size;
        $this->type    = $type;
        $this->error   = $error;

        if (!empty($key)) {
            $this->key = $key;
        }
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

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
     */
    public function getRealType(): string
    {
        if (empty($this->realType)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if (false !== $finfo) {
                $mime = finfo_file($finfo, $this->tmpName);

                if (false !== $mime) {
                    $this->realType = $mime;
                }
            }
        }

        return $this->realType;
    }

    /**
     * Returns the file size of the uploaded file
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the temporary name of the uploaded file
     */
    public function getTempName(): string
    {
        return $this->tmpName;
    }

    /**
     * Returns the mime type reported by the browser
     * This mime type is not completely secure, use getRealType() instead
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     */
    public function isUploadedFile(): bool
    {
        $name = $this->tmpName;

        return !empty($name) && is_uploaded_file($name);
    }

    /**
     * Moves the temporary file to a destination within the application
     */
    public function moveTo(string $destination): bool
    {
        return move_uploaded_file($this->tmpName, $destination);
    }
}
