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
 * @property bool   $local
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
     * @var array
     */
    protected array $assets = [];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * Should version be determined from file modification time
     *
     * @var bool
     */
    protected bool $autoVersion = false;

    /**
     * @var array
     */
    protected array $codes = [];

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var bool
     */
    protected bool $join = true;

    /**
     * @var bool
     */
    protected bool $local = true;

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
     * Returns the generator of the class
     *
     * @return Generator
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator(): Generator
    {
        foreach ($this->assets as $key => $value) {
            yield $key => $value;
        }
    }

    public function getPrefix(): string
    {
        return $this->prefix;
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
        return isset($this->assets[$asset->getAssetKey()]);
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
     * Adds a asset or inline-code to the collection
     *
     * @param AssetInterface $asset
     *
     * @return bool
     */
    final protected function addAsset(AssetInterface $asset): bool
    {
        if (true !== $this->has($asset)) {
            return false;
        }

        $assetKey   = $asset->getAssetKey();
        $collection = $this->assets;
        if ($asset instanceof Asset) {
            $collection = $this->codes;
        }

        $collection[$assetKey] = $asset;

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
