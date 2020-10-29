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

namespace Phalcon\Assets;

use Phalcon\Support\Traits\PhpFileTrait;

use function sha1;

/**
 * Represents an asset asset
 *
 *```php
 * $asset = new \Phalcon\Assets\Asset("js", "javascripts/jquery.js");
 *```
 *
 * @property array       $attributes
 * @property bool        $autoVersion
 * @property bool        $filter
 * @property bool        $local
 * @property string      $path
 * @property string      $sourcePath
 * @property string      $targetPath
 * @property string      $targetUri
 * @property string      $type
 * @property string|null $version
 *
 */
class Asset implements AssetInterface
{
    use PhpFileTrait;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var bool
     */
    protected bool $autoVersion = false;

    /**
     * @var bool
     */
    protected bool $filter;

    /**
     * @var bool
     */
    protected bool $local;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string
     */
    protected string $sourcePath;

    /**
     * @var string
     */
    protected string $targetPath;

    /**
     * @var string
     */
    protected string $targetUri;

    /**
     * @var string
     */
    protected string $type;

    /**
     * Version of resource
     *
     * @var string|null
     */
    protected ?string $version;

    /**
     * Asset constructor.
     *
     * @param string      $type
     * @param string      $path
     * @param bool        $local
     * @param bool        $filter
     * @param array       $attributes
     * @param string|null $version
     * @param bool        $autoVersion
     */
    public function __construct(
        string $type,
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = [],
        string $version = null,
        bool $autoVersion = false
    ) {
        $this->type        = $type;
        $this->path        = $path;
        $this->local       = $local;
        $this->filter      = $filter;
        $this->attributes  = $attributes;
        $this->version     = $version;
        $this->autoVersion = $autoVersion;
    }

    /**
     * Gets the asset's key.
     */
    public function getAssetKey(): string
    {
        $key = $this->getType() . ':' . $this->getPath();

        return sha1($key);
    }

    /**
     * Gets extra HTML attributes.
     *
     * @return array|null
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * Returns the content of the asset as an string
     * Optionally a base path where the asset is located can be set
     *
     * @param string|null $basePath
     *
     * @return string
     * @throws Exception
     */
    public function getContent(string $basePath = null): string
    {
        /**
         * A base path for assets can be set in the assets manager
         */
        $completePath = $basePath . $this->checkPath('sourcePath');

        /**
         * Local assets are loaded from the local disk
         */
        if (
            true === $this->local &&
            true !== $this->phpFileExists($completePath)
        ) {
            $this->throwException($completePath);
        }

        /**
         * Use file_get_contents to respect the openbase_dir. Access URLs must
         * be enabled
         */
        $content = $this->phpFileGetContents($completePath);

        if (false === $content) {
            $this->throwException($completePath);
        }

        return $content;
    }

    /**
     * Gets if the asset must be filtered or not.
     *
     * @return bool
     */
    public function getFilter(): bool
    {
        return $this->filter;
    }

    /**
     * Returns the path for this asset
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the complete location where the asset is located
     *
     * @param string|null $basePath
     *
     * @return string
     */
    public function getRealSourcePath(string $basePath = null): string
    {
        $source = $this->checkPath('sourcePath');
        if (true === $this->local) {
            /**
             * Get the real template path
             */
            return realpath($basePath . $source);
        }

        return $source;
    }

    /**
     * Returns the complete location where the asset must be written
     *
     * @param string|null $basePath
     *
     * @return string
     */
    public function getRealTargetPath(string $basePath = null): string
    {
        $target = $this->checkPath('targetPath');
        if (true === $this->local) {
            /**
             * A base path for assets can be set in the assets manager
             */
            $completePath = $basePath . $target;

            /**
             * Get the real template path, the target path can optionally don't
             * exist
             */
            if (true === $this->phpFileExists($completePath)) {
                return realpath($completePath);
            }

            return $completePath;
        }

        return $target;
    }

    /**
     * Returns the real target uri for the generated HTML
     *
     * @return string
     */
    public function getRealTargetUri(): string
    {
        $target = $this->checkPath('targetUri');
        $ver    = $this->version;
        if (true === $this->autoVersion && true === $this->local) {
            $modTime = filemtime($this->getRealSourcePath());
            $ver     = $ver ? $ver . '.' . $modTime : $modTime;
        }

        if (true !== empty($ver)) {
            $target = $target . "?ver=" . $ver;
        }

        return $target;
    }

    /**
     * Gets the asset's type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Checks if resource is using auto version
     *
     * @return bool
     */
    public function isAutoVersion(): bool
    {
        return $this->autoVersion;
    }

    /**
     * Sets extra HTML attributes
     *
     * @param array $attributes
     *
     * @return AssetInterface
     */
    public function setAttributes(array $attributes): AssetInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return AssetInterface
     */
    public function setAutoVersion(bool $flag): AssetInterface
    {
        $this->autoVersion = $flag;

        return $this;
    }

    /**
     * Sets if the asset must be filtered or not
     *
     * @param bool $filter
     *
     * @return AssetInterface
     */
    public function setFilter(bool $filter): AssetInterface
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Sets if the asset is local or external
     *
     * @param bool $local
     *
     * @return AssetInterface
     */
    public function setLocal(bool $local): AssetInterface
    {
        $this->local = $local;

        return $this;
    }

    /**
     * Sets the asset's source path
     *
     * @param string $sourcePath
     *
     * @return AssetInterface
     */
    public function setSourcePath(string $sourcePath): AssetInterface
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    /**
     * Sets the asset's target path
     *
     * @param string $targetPath
     *
     * @return AssetInterface
     */
    public function setTargetPath(string $targetPath): AssetInterface
    {
        $this->targetPath = $targetPath;

        return $this;
    }

    /**
     * Sets a target uri for the generated HTML
     *
     * @param string $targetUri
     *
     * @return AssetInterface
     */
    public function setTargetUri(string $targetUri): AssetInterface
    {
        $this->targetUri = $targetUri;

        return $this;
    }

    /**
     * Sets the asset's type
     *
     * @param string $type
     *
     * @return AssetInterface
     */
    public function setType(string $type): AssetInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Sets the asset's path
     *
     * @param string $path
     *
     * @return AssetInterface
     */
    public function setPath(string $path): AssetInterface
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param string $property
     *
     * @return string
     */
    private function checkPath(string $property): string
    {
        if (true === empty($this->$property)) {
            return $this->path;
        }

        return $this->$property;
    }

    /**
     * @param string $completePath
     *
     * @throws Exception
     */
    private function throwException(string $completePath): void
    {
        throw new Exception(
            'Asset\'s content for "' . $completePath . '" cannot be read'
        );
    }
}
