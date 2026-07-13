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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Phalcon\Assets\Traits\AttributesTrait;
use Phalcon\Assets\Traits\SourceTargetTrait;
use Phalcon\Traits\Php\FileTrait;
use Traversable;

use function realpath;

/**
 * Collection of asset objects
 *
 * @template TKey of array-key
 * @template TValue of AssetInterface
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements Countable, IteratorAggregate
{
    use AttributesTrait;
    use FileTrait;
    use SourceTargetTrait;

    /**
     * @var array<string, AssetInterface>
     */
    protected array $assets = [];

    /**
     * Should version be determined from file modification time
     *
     * @var bool
     */
    protected bool $autoVersion = false;

    /**
     * @var AssetInterface[]
     */
    protected array $codes = [];

    /**
     * @var FilterInterface[]
     */
    protected array $filters = [];

    /**
     * @var bool
     */
    protected bool $join = true;

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var bool
     */
    protected bool $targetIsLocal = true;

    /**
     * @var string
     */
    protected string $version = '';

    /**
     * Adds an asset to the collection
     *
     * @param AssetInterface $asset
     *
     * @return $this
     */
    public function add(AssetInterface $asset): static
    {
        $this->addAsset($asset);

        return $this;
    }

    /**
     * Adds a CSS asset to the collection
     *
     * @param string                $path
     * @param bool|null             $isLocal
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     *
     * @return static
     */
    public function addCss(
        string $path,
        bool | null $isLocal = null,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ): static {
        return $this->processAdd(
            "Css",
            $path,
            $isLocal,
            $filter,
            $attributes,
            $version,
            $autoVersion
        );
    }

    /**
     * Adds a filter to the collection
     *
     * @param FilterInterface $filter
     *
     * @return static
     */
    public function addFilter(FilterInterface $filter): static
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Adds an inline code to the collection
     *
     * @param Inline $code
     *
     * @return static
     */
    public function addInline(Inline $code): static
    {
        $this->addAsset($code);

        return $this;
    }

    /**
     * Adds an inline CSS to the collection
     *
     * @param string                $content
     * @param bool                  $filter
     * @param array<string, string> $attributes
     *
     * @return static
     */
    public function addInlineCss(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): static {
        return $this->processAddInline("Css", $content, $filter, $attributes);
    }

    /**
     * Adds an inline JavaScript to the collection
     *
     * @param string                $content
     * @param bool                  $filter
     * @param array<string, string> $attributes
     *
     * @return static
     */
    public function addInlineJs(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): static {
        return $this->processAddInline("Js", $content, $filter, $attributes);
    }

    /**
     * Adds a JavaScript asset to the collection
     *
     * @param string                $path
     * @param bool|null             $isLocal
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     *
     * @return static
     */
    public function addJs(
        string $path,
        bool | null $isLocal = null,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ): static {
        return $this->processAdd(
            "Js",
            $path,
            $isLocal,
            $filter,
            $attributes,
            $version,
            $autoVersion
        );
    }

    /**
     * Return the count of the assets
     *
     * @return int
     *
     * @link https://php.net/manual/en/countable.count.php
     */
    public function count(): int
    {
        return count($this->assets);
    }

    /**
     * Return the stored assets
     *
     * @return array<string, AssetInterface>
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Return the stored codes
     *
     * @return AssetInterface[]
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * Return the stored filters
     *
     * @return FilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the iterator of the class
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->assets);
    }

    /**
     * @return bool
     */
    public function getJoin(): bool
    {
        return $this->join;
    }

    /**
     * Returns the prefix
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
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
            /**
             * Just in case realPath returns false, cast it to an empty string
             */
            return (string)realPath($completePath);
        }

        return $completePath;
    }

    /**
     * Returns whether the target is local or not
     *
     * @return bool
     */
    public function getTargetIsLocal(): bool
    {
        return $this->targetIsLocal;
    }

    /**
     * Returns the version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Checks this the asset is added to the collection.
     *
     * ```php
     * use Phalcon\Assets\Asset;
     * use Phalcon\Assets\Collection;
     *
     * $collection = new Collection();
     *
     * $asset = new Asset("js", "js/jquery.js");
     *
     * $collection->add($asset);
     * $collection->has($asset); // true
     * ```
     *
     * @param AssetInterface $asset
     *
     * @return bool
     */
    public function has(AssetInterface $asset): bool
    {
        $key = $asset->getAssetKey();

        return isset($this->assets[$key]);
    }

    /**
     * Checks if collection is using auto version
     *
     * @return bool
     */
    public function isAutoVersion(): bool
    {
        return $this->autoVersion;
    }

    /**
     * Sets if all filtered assets in the collection must be joined in a single
     * result file
     *
     * @param bool $flag
     *
     * @return static
     */
    public function join(bool $flag): static
    {
        $this->join = $flag;

        return $this;
    }

    /**
     * Sets extra HTML attributes
     *
     * @param array<string, string> $attributes
     *
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return static
     */
    public function setAutoVersion(bool $flag): static
    {
        $this->autoVersion = $flag;

        return $this;
    }

    /**
     * Sets an array of filters in the collection
     *
     * @param FilterInterface[] $filters
     *
     * @return static
     */
    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Sets a common prefix for all the assets
     *
     * @param string $prefix
     *
     * @return static
     */
    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Sets if the target local or not
     *
     * @param bool $flag
     *
     * @return static
     */
    public function setTargetIsLocal(bool $flag): static
    {
        $this->targetIsLocal = $flag;

        return $this;
    }

    /**
     * Sets the version
     *
     * @param string $version
     *
     * @return static
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Adds an asset or inline-code to the collection
     *
     * @param AssetInterface $asset
     *
     * @return bool
     */
    final protected function addAsset(AssetInterface $asset): bool
    {
        if (true === $this->has($asset)) {
            return false;
        }

        if ($asset instanceof Asset) {
            $this->assets[$asset->getAssetKey()] = $asset;

            return true;
        }

        $this->codes[] = $asset;

        return true;
    }

    /**
     * Adds an inline asset
     *
     * @param string                $className
     * @param string                $path
     * @param bool|null             $isLocal
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     *
     * @return static
     */
    private function processAdd(
        string $className,
        string $path,
        bool | null $isLocal = null,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ): static {
        /** @var class-string $name */
        $name = "Phalcon\\Assets\\Asset\\" . $className;

        /** @var AssetInterface $add */
        $add = new $name(
            $path,
            (null !== $isLocal) ? $isLocal : $this->isLocal,
            $filter,
            $this->processAttributes($attributes),
            $version,
            $autoVersion
        );

        $this->add($add);

        return $this;
    }

    /**
     * Adds an inline asset
     *
     * @param string                $className
     * @param string                $content
     * @param bool                  $filter
     * @param array<string, string> $attributes
     *
     * @return static
     */
    private function processAddInline(
        string $className,
        string $content,
        bool $filter = true,
        array $attributes = []
    ): static {
        /** @var class-string $name */
        $name  = "Phalcon\\Assets\\Inline\\" . $className;
        $attrs = $this->processAttributes($attributes);
        /** @var Inline $asset */
        $asset = new $name(
            $content,
            $filter,
            $attrs
        );

        $this->codes[$asset->getAssetKey()] = $asset;

        return $this;
    }

    /**
     * @param array<string, string> $attributes
     *
     * @return array<string, string>
     */
    private function processAttributes(array $attributes): array
    {
        return (!empty($attributes)) ? $attributes : $this->attributes;
    }
}
