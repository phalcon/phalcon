<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Http\Request;



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

    protected string $error;

    protected string $name;
        
    protected string $extension;

    protected string $key;

    protected string $realType;

    protected int $size;

    protected string $tmp;

    protected string $type;

    /**
     * Phalcon\Http\Request\File constructor
     */
    public function __construct(
        array $file, 
        ?string $key = null)
    {
        $name = $file["name"] ?? null;

        if ($name !== null) {
            $this->name = $name;
            $this->extension = pathinfo($name, PATHINFO_EXTENSION);
        }
        else {
            $this->name = '';
            $this->extension = '';
        }

        $this->tmp   = $file ["tmp_name"] ?? '';
        $this->size  = $file ["size"] ?? 0;
        $this->type  = $file ["type"] ?? '';
        $this->error = $file ["error"] ?? '';
        $this->key = $key ? $key : '';

    }

    /**
     * Returns the real name of the uploaded file
     */
    public function getName(): string
    {
        return $this->name;
    }

    /** Not in the FileInterface, but is protected property */
    public function getKey(): string {
        return $this->key;
    }
    /**
     * Gets the real mime type of the upload file using finfo
     */
    public function getRealType(): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!is_resource($finfo)) {
            return "";
        }

        $mime = finfo_file($finfo, $this->tmp);

        finfo_close($finfo);

        return $mime;
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
    public function getTempName() : string
    {
        return $this->tmp;
    }

    /**
     * Returns the mime type reported by the browser
     * This mime type is not completely secure, use getRealType() instead
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Checks whether the file has been uploaded via Post.
     */
    public function isUploadedFile() : bool
    {
        $tmp = $this->getTempName();

        return (is_string($tmp) 
            && is_uploaded_file($tmp));
    }

    /**
     * Moves the temporary file to a destination within the application
     */
    public function moveTo(string $destination) : bool
    {
        return move_uploaded_file($this->tmp, $destination);
    }
    
    public function getError() : string {
        return $this->error;
    }
}
