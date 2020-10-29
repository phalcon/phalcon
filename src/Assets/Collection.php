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

use Countable;
use IteratorAggregate;
use Phalcon\Assets\Asset\Css as AssetCss;
use Phalcon\Assets\Asset\Js as AssetJs;
use Phalcon\Assets\Inline\Css as InlineCss;
use Phalcon\Assets\Inline\Js as InlineJs;
use Phalcon\Assets\Traits\CollectionAddTrait;
use Phalcon\Support\Traits\PhpFileTrait;

use function realpath;

/**
 * Class Collection
 *
 * @package Phalcon\Assets
 */
class Collection implements Countable, IteratorAggregate
{
    use PhpFileTrait;
    use CollectionAddTrait;

    /**
     * Adds a asset to the collection
     *
     * @param AssetInterface $asset
     *
     * @return $this
     */
    public function add(AssetInterface $asset): Collection
    {
        $this->addAsset($asset);

        return $this;
    }

    /**
     * Adds a CSS asset to the collection
     *
     * @param string      $path
     * @param bool|null   $local
     * @param bool        $filter
     * @param array       $attributes
     * @param string|null $version
     * @param bool        $autoVersion
     *
     * @return $this
     */
    public function addCss(
        string $path,
        bool $local = null,
        bool $filter = true,
        array $attributes = [],
        string $version = null,
        bool $autoVersion = false
    ): Collection {
        $isLocal = (null !== $local) ? $local : $this->local;
        $attrs   = $this->processAttributes($attributes);

        $this->add(
            new AssetCss($path, $isLocal, $filter, $attrs, $version, $autoVersion)
        );

        return $this;
    }

    /**
     * Adds a filter to the collection
     *
     * @param FilterInterface $filter
     *
     * @return Collection
     */
    public function addFilter(FilterInterface $filter): Collection
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Adds an inline code to the collection
     *
     * @param Inline $code
     *
     * @return $this
     */
    public function addInline(Inline $code): Collection
    {
        $this->addAsset($code);

        return $this;
    }

    /**
     * Adds an inline CSS to the collection
     *
     * @param string $content
     * @param bool   $filter
     * @param array  $attributes
     *
     * @return $this
     */
    public function addInlineCss(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): Collection {
        $attrs = $this->processAttributes($attributes);
        $asset = new InlineCss(
            $content,
            $filter,
            $attrs
        );

        $this->codes[$asset->getAssetKey()] = $asset;

        return $this;
    }

    /**
     * Adds an inline JavaScript to the collection
     *
     * @param string $content
     * @param bool   $filter
     * @param array  $attributes
     *
     * @return $this
     */
    public function addInlineJs(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): Collection {
        $attrs = $this->processAttributes($attributes);
        $asset = new InlineJs(
            $content,
            $filter,
            $attrs
        );

        $this->codes[$asset->getAssetKey()] = $asset;

        return $this;
    }

    /**
     * Adds a JavaScript asset to the collection
     *
     * @param string      $path
     * @param bool|null   $local
     * @param bool        $filter
     * @param array       $attributes
     * @param string|null $version
     * @param bool        $autoVersion
     *
     * @return $this
     */
    public function addJs(
        string $path,
        bool $local = null,
        bool $filter = true,
        array $attributes = [],
        string $version = null,
        bool $autoVersion = false
    ): Collection {
        $isLocal = (null !== $local) ? $local : $this->local;
        $attrs   = $this->processAttributes($attributes);

        $this->add(
            new AssetJs($path, $isLocal, $filter, $attrs, $version, $autoVersion)
        );

        return $this;
    }

    /**
     * Returns the complete location where the joined/filtered collection must
     * be written
     *
     * @param string $basePath
     *
     * @return string
     */
    public function getRealTargetPath(string $basePath): string
    {
        /**
         * A base path for assets can be set in the assets manager
         */
        $completePath = $basePath . $this->targetPath;

        /**
         * Get the real template path, the target path can optionally don't
         * exist
         */
        if (true === $this->phpFileExists($completePath)) {
            return realPath($completePath);
        }

        return $completePath;
    }

    /**
     * Sets if all filtered assets in the collection must be joined in a single
     * result file
     *
     * @param bool $join
     *
     * @return Collection
     */
    public function join(bool $join): Collection
    {
        $this->join = $join;

        return $this;
    }

    /**
     * Sets extra HTML attributes
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes): Collection
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return Collection
     */
    public function setAutoVersion(bool $flag): Collection
    {
        $this->autoVersion = $flag;

        return $this;
    }

    /**
     * Sets an array of filters in the collection
     *
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters): Collection
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Sets if the collection uses local assets by default
     *
     * @param bool $local
     *
     * @return $this
     */
    public function setLocal(bool $local): Collection
    {
        $this->local = $local;

        return $this;
    }

    /**
     * Sets a common prefix for all the assets
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix(string $prefix): Collection
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Sets the target local
     *
     * @param bool $targetLocal
     *
     * @return $this
     */
    public function setTargetLocal(bool $targetLocal): Collection
    {
        $this->targetLocal = $targetLocal;

        return $this;
    }

    /**
     * Sets the target path of the file for the filtered/join output
     *
     * @param string $targetPath
     *
     * @return $this
     */
    public function setTargetPath(string $targetPath): Collection
    {
        $this->targetPath = $targetPath;

        return $this;
    }

    /**
     * Sets a target uri for the generated HTML
     *
     * @param string $targetUri
     *
     * @return Collection
     */
    public function setTargetUri(string $targetUri): Collection
    {
        $this->targetUri = $targetUri;

        return $this;
    }

    /**
     * Sets a base source path for all the assets in this collection
     *
     * @param string $sourcePath
     *
     * @return Collection
     */
    public function setSourcePath(string $sourcePath): Collection
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }
}
