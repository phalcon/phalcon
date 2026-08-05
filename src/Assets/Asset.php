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

use Phalcon\Assets\Exceptions\CannotReadAsset;
use Phalcon\Assets\Traits\AttributesTrait;
use Phalcon\Assets\Traits\SourceTargetTrait;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Traits\Php\HashTrait;

/**
 * Object representation of an asset
 *
 *```php
 * $asset = new \Phalcon\Assets\Asset("js", "js/jquery.js");
 *```
 */
class Asset implements AssetInterface
{
    use AttributesTrait;
    use FileTrait;
    use HashTrait;
    use SourceTargetTrait;

    /**
     * Asset constructor.
     *
     * @param array<string, string> $attributes
     */
    public function __construct(
        protected string $type,
        protected string $path,
        bool $isLocal = true,
        protected bool $filter = true,
        array $attributes = [],
        protected string | null $version = null,
        protected bool $isAutoVersion = false
    ) {
        $this->isLocal    = $isLocal;
        $this->attributes = $attributes;
    }

    /**
     * Gets the asset's key.
     */
    public function getAssetKey(): string
    {
        $key = $this->getType() . ':' . $this->getPath();

        return $this->phpHash("sha256", $key);
    }

    /**
     * Gets extra HTML attributes.
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
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
    public function getContent(string | null $basePath = null): string
    {
        /**
         * A base path for assets can be set in the assets manager
         */
        $completePath = $basePath . $this->checkPath("sourcePath");

        /**
         * Local assets are loaded from the local disk
         */
        if (
            true === $this->isLocal &&
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

        /** @var string $content */
        return $content;
    }

    /**
     * Gets if the asset must be filtered or not.
     */
    public function getFilter(): bool
    {
        return $this->filter;
    }

    /**
     * Returns the path for this asset
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
    public function getRealSourcePath(string | null $basePath = null): string
    {
        $source = $this->checkPath("sourcePath");
        if (true === $this->isLocal) {
            /**
             * Get the real template path. If `realpath` fails it will return
             * `false`. Casting it to a string will return an empty string
             */
            $source = (string)realpath($basePath . $source);
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
    public function getRealTargetPath(string | null $basePath = null): string
    {
        $target = $this->checkPath("targetPath");
        if (true === $this->isLocal) {
            /**
             * A base path for assets can be set in the assets manager
             */
            $completePath = $basePath . $target;

            /**
             * Get the real template path, the target path can optionally don't
             * exist
             */
            if (true === $this->phpFileExists($completePath)) {
                $completePath = realpath($completePath);

                if (false === $completePath) {
                    $completePath = '';
                }
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
        $target = $this->checkPath("targetUri");
        $ver    = $this->version;
        if (true === $this->isAutoVersion && true === $this->isLocal) {
            $modTime = filemtime($this->getRealSourcePath());
            $ver     = $ver ? $ver . '.' . $modTime : $modTime;
        }

        if (!empty($ver)) {
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
     * Gets the asset's version.
     *
     * @return string|null
     */
    public function getVersion(): string | null
    {
        return $this->version;
    }

    /**
     * Checks if the asset is using auto version
     *
     * @return bool
     */
    public function isAutoVersion(): bool
    {
        return $this->isAutoVersion;
    }

    /**
     * Sets extra HTML attributes
     *
     * @param array<string, string> $attributes
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
        $this->isAutoVersion = $flag;

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
     * Sets the asset's version
     *
     * @param string $version
     *
     * @return AssetInterface
     */
    public function setVersion(string $version): AssetInterface
    {
        $this->version = $version;

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
        throw new CannotReadAsset($completePath);
    }
}
