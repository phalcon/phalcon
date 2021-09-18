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

namespace Phalcon\Assets\Traits;

use Generator;
use Phalcon\Assets\Asset;
use Phalcon\Assets\AssetInterface;
use Phalcon\Assets\FilterInterface;

/**
 * Class Collection
 *
 * @package Phalcon\Assets
 *
 * @property array  $assets
 * @property array  $attributes
 * @property bool   $autoVersion
 * @property array  $codes
 * @property array  $filters
 * @property bool   $join
 * @property bool   $isLocal
 * @property string $prefix
 * @property int    $position
 * @property string $sourcePath
 * @property bool   $targetLocal
 * @property string $targetPath
 * @property string $targetUri
 * @property string $version
 */
trait CollectionAddTrait
{
    /**
     * @var array<string, AssetInterface>
     */
    protected array $assets = [];

    /**
     * @var array<string, string>
     */
    protected array $attributes = [];

    /**
     * Should version be determined from file modification time
     *
     * @var bool
     */
    protected bool $autoVersion = false;

    /**
     * @var array<string, string>
     */
    protected array $codes = [];

    /**
     * @var array<int, FilterInterface>
     */
    protected array $filters = [];

    /**
     * @var bool
     */
    protected bool $join = true;

    /**
     * @var bool
     */
    protected bool $isLocal = true;

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * @var string
     */
    protected string $sourcePath = '';

    /**
     * @var bool
     */
    protected bool $targetLocal = true;

    /**
     * @var string
     */
    protected string $targetPath = '';

    /**
     * @var string
     */
    protected string $targetUri = '';

    /**
     * @var string
     */
    protected string $version = '';

    /**
     * Return the count of the assets
     *
     * @return int|void
     *
     * @link https://php.net/manual/en/countable.count.php
     */
    public function count()
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
     * Return the stored attributes
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Return the stored codes
     *
     * @return array<string, string>
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * Return the stored filters
     *
     * @return array<int, FilterInterface>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the generator of the class
     *
     * @return Generator<int, mixed>
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator(): Generator
    {
        foreach ($this->assets as $key => $value) {
            yield $key => $value;
        }
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
     * Returns the source path
     *
     * @return string
     */
    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    /**
     * Returns whether the target is local or not
     *
     * @return bool
     */
    public function getTargetLocal(): bool
    {
        return $this->targetLocal;
    }

    /**
     * Returns the target path
     *
     * @return string
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * Returns the target Uri
     *
     * @return string
     */
    public function getTargetUri(): string
    {
        return $this->targetUri;
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
        foreach ($this->assets as $storedAsset) {
            if ($key === $storedAsset->getAssetKey()) {
                return true;
            }
        }

        return false;
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
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->isLocal;
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
     * @param array $attributes
     *
     * @return array
     */
    private function processAttributes(array $attributes): array
    {
        return (true !== empty($attributes)) ? $attributes : $this->attributes;
    }
}
