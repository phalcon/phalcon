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

use Phalcon\Assets\Asset\Css as AssetCss;
use Phalcon\Assets\Asset\Js as AssetJs;
use Phalcon\Assets\Exceptions\AssetSourceTargetCollision;
use Phalcon\Assets\Exceptions\CollectionNotFound;
use Phalcon\Assets\Exceptions\InvalidAssetSourcePath;
use Phalcon\Assets\Exceptions\InvalidAssetTargetPath;
use Phalcon\Assets\Exceptions\InvalidFilter;
use Phalcon\Assets\Exceptions\InvalidTargetPath;
use Phalcon\Assets\Exceptions\TargetPathIsDirectory;
use Phalcon\Assets\Inline\Css as InlineCss;
use Phalcon\Assets\Inline\Js as InlineJs;
use Phalcon\Contracts\Assets\AssetsTypes;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Html\Helper\Link;
use Phalcon\Html\Helper\Script;
use Phalcon\Html\TagFactory;
use Phalcon\Mvc\Url;
use Phalcon\Traits\Php\FileTrait;

use function call_user_func_array;
use function filemtime;
use function is_array;
use function is_dir;

use const PHP_EOL;

/**
 * Manages collections of CSS/JavaScript assets
 *
 * @phpstan-import-type assets_asset_list from AssetsTypes
 * @phpstan-import-type assets_attributes from AssetsTypes
 * @phpstan-import-type assets_callback from AssetsTypes
 * @phpstan-import-type assets_collections from AssetsTypes
 * @phpstan-import-type assets_filters from AssetsTypes
 * @phpstan-import-type assets_options from AssetsTypes
 * @phpstan-import-type assets_parameters from AssetsTypes
 */
class Manager extends AbstractInjectionAware
{
    use FileTrait;

    /**
     * @var assets_collections
     */
    protected array $collections   = [];
    protected bool $implicitOutput = true;

    /**
     * Manager constructor.
     *
     * @param TagFactory     $tagFactory
     * @param assets_options $options
     */
    public function __construct(
        protected TagFactory $tagFactory,
        protected array $options = []
    ) {
    }

    /**
     * Adds a raw asset to the manager
     *
     * @param Asset $asset
     */
    public function addAsset(Asset $asset): static
    {
        /**
         * Adds the asset by its type
         */
        $this->addAssetByType($asset->getType(), $asset);

        return $this;
    }

    /**
     * Adds an asset by its type
     *
     * @param string $type
     * @param Asset  $asset
     */
    public function addAssetByType(string $type, Asset $asset): static
    {
        $collection = $this->checkAndCreateCollection($type);
        $collection->add($asset);

        return $this;
    }

    /**
     * Adds a CSS asset to the 'css' collection
     *
     * @param assets_attributes $attributes
     */
    public function addCss(
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ): static {
        $this->addAssetByType(
            'css',
            new AssetCss($path, $local, $filter, $attributes, $version, $autoVersion)
        );

        return $this;
    }

    /**
     * Adds a raw inline code to the manager
     *
     * @param Inline $code
     */
    public function addInlineCode(Inline $code): static
    {
        /**
         * Adds the inline code by its type
         */
        $this->addInlineCodeByType($code->getType(), $code);

        return $this;
    }

    /**
     * Adds an inline code by its type
     *
     * @param string $type
     * @param Inline $code
     */
    public function addInlineCodeByType(string $type, Inline $code): static
    {
        $collection = $this->checkAndCreateCollection($type);
        $collection->addInline($code);

        return $this;
    }

    /**
     * Adds an inline CSS to the 'css' collection
     *
     * @param assets_attributes $attributes
     */
    public function addInlineCss(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): static {
        $this->addInlineCodeByType(
            'css',
            new InlineCss($content, $filter, $attributes)
        );

        return $this;
    }

    /**
     * Adds an inline JavaScript to the 'js' collection
     *
     * @param assets_attributes $attributes
     */
    public function addInlineJs(
        string $content,
        bool $filter = true,
        array $attributes = []
    ): static {
        $this->addInlineCodeByType(
            'js',
            new InlineJs($content, $filter, $attributes)
        );

        return $this;
    }

    /**
     * Adds a JavaScript asset to the 'js' collection
     *
     *```php
     * $assets->addJs("scripts/jquery.js");
     * $assets->addJs("https://jquery.my-cdn.com/jquery.js", false);
     *```
     *
     * @param assets_attributes $attributes
     */
    public function addJs(
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = [],
        string | null $version = null,
        bool $autoVersion = false
    ): static {
        $this->addAssetByType(
            'js',
            new AssetJs($path, $local, $filter, $attributes, $version, $autoVersion)
        );

        return $this;
    }

    /**
     * Creates/Returns a collection of assets
     */
    public function collection(string $name): Collection
    {
        return $this->checkAndCreateCollection($name);
    }

    /**
     * Creates/Returns a collection of assets by type
     *
     * The `instanceof` guard below is the validation, so the parameter stays a
     * plain array here.
     *
     * @param  array<array-key, mixed> $assets
     * @return assets_asset_list
     */
    public function collectionAssetsByType(array $assets, string $type): array
    {
        $filtered = [];
        foreach ($assets as $asset) {
            if (
                $asset instanceof AssetInterface &&
                $type === $asset->getType()
            ) {
                $filtered[] = $asset;
            }
        }

        return $filtered;
    }

    /**
     * Returns true or false if collection exists.
     *
     * ```php
     * if ($manager->exists("jsHeader")) {
     *     // \Phalcon\Assets\Collection
     *     $collection = $manager->get("jsHeader");
     * }
     * ```
     *
     * @deprecated
     */
    public function exists(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Returns a collection by its id.
     *
     * ```php
     * $scripts = $assets->get("js");
     * ```
     */
    public function get(string $name): Collection
    {
        if (!isset($this->collections[$name])) {
            throw new CollectionNotFound($name);
        }

        return $this->collections[$name];
    }

    /**
     * Returns existing collections in the manager
     *
     * @return assets_collections
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    /**
     * Returns the CSS collection of assets
     */
    public function getCss(): Collection
    {
        return $this->checkAndCreateCollection('css');
    }

    /**
     * Returns the CSS collection of assets
     */
    public function getJs(): Collection
    {
        return $this->checkAndCreateCollection('js');
    }

    /**
     * Returns the manager options
     *
     * @return assets_options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns true or false if collection exists.
     *
     * ```php
     * if ($manager->has("jsHeader")) {
     *     // \Phalcon\Assets\Collection
     *     $collection = $manager->get("jsHeader");
     * }
     * ```
     */
    public function has(string $name): bool
    {
        return isset($this->collections[$name]);
    }

    /**
     * Traverses a collection calling the callback to generate its HTML
     */
    public function output(Collection $collection, string $type): string | null
    {
        $completeSourcePath    = '';
        $completeTargetPath    = '';
        $filteredContent       = '';
        $filteredJoinedContent = '';
        $join                  = false;
        $output                = '';

        $callbackMethod = ('css' === $type) ? 'cssLink' : 'jsLink';
        $callback       = [$this, $callbackMethod];

        /**
         * Get the assets as an array
         */
        $assets = $this->collectionAssetsByType(
            $collection->getAssets(),
            $type
        );

        /**
         * Get filters in the collection
         */
        $filters = $collection->getFilters();
        $typeCss = 'css';

        /**
         * Prepare options if the collection must be filtered
         */
        if (!empty($filters)) {
            /**
             * Check for global options in the asset manager. The source and
             * target base path are global locations where all assets are read
             * and written respectively
             */
            $sourceBasePath = $this->options['sourceBasePath'] ?? '';
            $targetBasePath = $this->options['targetBasePath'] ?? '';
            /**
             * Check if the collection have its own source base path
             */
            $collectionSourcePath = $collection->getSourcePath();
            $completeSourcePath   = $sourceBasePath;

            /**
             * Concatenate the global base source path with the collection one
             */
            if (!empty($collectionSourcePath)) {
                $completeSourcePath .= $collectionSourcePath;
            }

            /**
             * Check if the collection have its own target base path
             */
            $collectionTargetPath = $collection->getTargetPath();
            $completeTargetPath   = $targetBasePath;

            /**
             * Concatenate the global base source path with the collection one
             */
            if (!empty($collectionTargetPath)) {
                $completeTargetPath .= $collectionTargetPath;
            }

            /**
             * Check if the collection have its own target base path
             */
            $join = $this->getJoin($collection, $completeTargetPath);
        }

        /** @var Asset $asset */
        foreach ($assets as $asset) {
            $filterNeeded = false;

            /**
             * If the collection must not be joined we must print HTML for
             * each one
             */
            if (!empty($filters)) {
                $sourcePath = $asset->getPath();
                if (true === $asset->isLocal()) {
                    $filterNeeded = true;
                    /**
                     * Get the complete path
                     */
                    $sourcePath = $this->getSourcePath($asset, $completeSourcePath);
                }

                /**
                 * Get the target path, we need to write the filtered content to
                 * a file
                 */
                $targetPath = $asset->getRealTargetPath($completeTargetPath);

                /**
                 * We need a valid final target path
                 */
                if (empty($targetPath)) {
                    throw new InvalidAssetTargetPath($asset->getPath());
                }

                $filterNeeded = $this->isFilterNeeded($asset, $targetPath, $sourcePath, $filterNeeded);
            } else {
                /**
                 * If there are no filters, just print/buffer the HTML
                 */
                $prefixedPath = $this->calculatePrefixedPath(
                    $collection,
                    $asset->getRealTargetUri(),
                    $asset->getRealSourcePath()
                );

                /**
                 * Generate the HTML
                 */
                $html = $this->doCallback(
                    $callback,
                    $asset->getAttributes(),
                    $prefixedPath,
                    $asset->isLocal()
                );

                /**
                 * Implicit output prints the content directly
                 */
                if (true === $this->implicitOutput) {
                    echo $html;
                } else {
                    $output .= $html;
                }

                continue;
            }

            if (true === $filterNeeded) {
                /**
                 * Gets the asset's content
                 */
                $content = $asset->getContent($completeSourcePath);

                /**
                 * Apply the collection filters; the asset opts in via its own
                 * filter flag
                 */
                $mustFilter      = $asset->getFilter();
                $filteredContent = $this->applyFilters($content, $filters, $mustFilter);

                /**
                 * Update the joined filtered content
                 */
                if (true === $join) {
                    $filteredJoinedContent .= $filteredContent;
                    if (true === $mustFilter && $asset->getType() !== $typeCss) {
                        $filteredJoinedContent .= ';';
                    }
                }

                if (true !== $join) {
                    /**
                     * Write the file using file-put-contents. This respects the
                     * openbase-dir also writes to streams
                     */
                    $this->phpFilePutContents($targetPath, $filteredContent);
                }
            }

            if (true !== $join) {
                /**
                 * Generate the HTML using the original path in the asset
                 */
                $prefixedPath = $this->calculatePrefixedPath(
                    $collection,
                    $asset->getRealTargetUri(),
                    $asset->getRealSourcePath()
                );

                /**
                 * Generate the HTML
                 */
                $html = $this->doCallback(
                    $callback,
                    $collection->getAttributes(),
                    $prefixedPath,
                    true
                );

                /**
                 * Implicit output prints the content directly
                 */
                if (true === $this->implicitOutput) {
                    echo $html;
                } else {
                    $output .= $html;
                }
            }
        }

        if (
            !empty($filters) &&
            true === $join
        ) {
            /**
             * A symbolic link at the target file sends the write outside the
             * assets directory. Refuse it.
             */
            if (true === is_link($completeTargetPath)) {
                throw new InvalidAssetTargetPath($completeTargetPath);
            }

            /**
             * Write the file using file_put_contents. This respects the
             * openbase-dir also writes to streams
             */
            $this->phpFilePutContents($completeTargetPath, $filteredJoinedContent);

            $output = $this->getOutput($collection, $completeTargetPath, $callback, $output);
        }

        return $output;
    }

    /**
     * Prints the HTML for CSS assets
     *
     * @throws Exception
     */
    public function outputCss(string | null $name = null): string
    {
        $collection = $this->getCss();
        if (!empty($name)) {
            $collection = $this->get($name);
        }

        return (string)$this->output($collection, 'css');
    }

    /**
     * Traverses a collection and generate its HTML
     *
     * @param Collection $collection
     * @param string     $type
     *
     * @return string
     * @throws Exception
     */
    public function outputInline(Collection $collection, mixed $type): string
    {
        $output        = "";
        $html          = "";
        $joinedContent = "";
        $attributes    = [];
        $codes         = $collection->getCodes();
        $filters       = $collection->getFilters();
        $join          = $collection->getJoin();

        if (!empty($codes)) {
            /** @var Inline $code */
            foreach ($codes as $code) {
                $attributes = $code->getAttributes();
                $content    = $code->getContent();

                /**
                 * Apply the collection filters. The per-code filter flag is
                 * intentionally not honored here to preserve current behavior.
                 */
                $content = $this->applyFilters($content, $filters, true);

                if (true === $join) {
                    $joinedContent .= $content;
                } else {
                    $html .= $this->tagFactory->element(
                        $type,
                        $content,
                        $attributes,
                        true
                    ) . PHP_EOL;
                }
            }

            if (true === $join) {
                $html .= $this->tagFactory->element(
                    $type,
                    $joinedContent,
                    $attributes,
                    true
                ) . PHP_EOL;
            }

            /**
             * Implicit output prints the content directly
             */
            if (true === $this->implicitOutput) {
                echo $html;
            } else {
                $output .= $html;
            }
        }

        return $output;
    }

    /**
     * Prints the HTML for inline CSS
     */
    public function outputInlineCss(string | null $name = null): string
    {
        $collection = $this->getCss();
        if (!empty($name)) {
            $collection = $this->get($name);
        }

        return $this->outputInline($collection, 'style');
    }

    /**
     * Prints the HTML for inline JS
     */
    public function outputInlineJs(string | null $name = null): string
    {
        $collection = $this->getJs();
        if (!empty($name)) {
            $collection = $this->get($name);
        }

        return $this->outputInline($collection, 'script');
    }

    /**
     * Prints the HTML for JS assets
     */
    public function outputJs(string | null $name = null): string
    {
        $collection = $this->getJs();
        if (!empty($name)) {
            $collection = $this->get($name);
        }

        return (string)$this->output($collection, 'js');
    }

    /**
     * Sets a collection in the Assets Manager
     *
     *```php
     * $assets->set("js", $collection);
     *```
     */
    public function set(string $name, Collection $collection): static
    {
        $this->collections[$name] = $collection;

        return $this;
    }

    /**
     * Sets the manager options
     *
     * @param assets_options $options
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets if the HTML generated must be directly printed or returned
     */
    public function useImplicitOutput(bool $implicitOutput): static
    {
        $this->implicitOutput = $implicitOutput;

        return $this;
    }

    /**
     * Applies the collection filters to the content. Filtering only happens
     * when `$mustFilter` is true; every filter must be a `FilterInterface`
     * instance.
     *
     * The `instanceof` guard below is the validation, so the parameter stays a
     * plain array here.
     *
     * @param array<array-key, mixed> $filters
     *
     * @throws InvalidFilter
     */
    private function applyFilters(
        string $content,
        array $filters,
        bool $mustFilter = true
    ): string {
        if (true !== $mustFilter) {
            return $content;
        }

        foreach ($filters as $filter) {
            /**
             * Filters must be valid FilterInterface instances
             */
            if (!($filter instanceof FilterInterface)) {
                throw new InvalidFilter();
            }

            /**
             * Calls the method 'filter' which must return a filtered version
             * of the content
             */
            $content = $filter->filter($content);
        }

        return $content;
    }

    /**
     * Calculates the prefixed path including the version
     */
    private function calculatePrefixedPath(
        Collection $collection,
        string $path,
        string $filePath
    ): string {
        $prefixedPath = $collection->getPrefix() . $path;
        $version      = $collection->getVersion();

        if (
            true === $collection->isAutoVersion() &&
            true === $collection->isLocal()
        ) {
            $modificationTime = filemtime($filePath);
            $version          = $version ? $version . '.' . $modificationTime : $modificationTime;
        }

        if ($version) {
            $prefixedPath = $prefixedPath . '?ver=' . $version;
        }

        return $prefixedPath;
    }

    private function checkAndCreateCollection(string $type): Collection
    {
        if (!isset($this->collections[$type])) {
            $this->collections[$type] = new Collection();
        }

        return $this->collections[$type];
    }

    /**
     * Builds a LINK[rel="stylesheet"] tag
     *
     * @throws Exception
     */
    private function cssLink(mixed $parameters = [], bool $local = true): string
    {
        return $this->processParameters(
            $parameters,
            $local,
            "link",
            "text/css",
            "href"
        );
    }

    /**
     * The native type stays `mixed`, matching the Zephir `var callback`.
     *
     * @param assets_callback   $callback
     * @param assets_attributes $attributes
     */
    private function doCallback(
        mixed $callback,
        array $attributes,
        string $prefixedPath,
        bool $local
    ): string {
        /**
         * Prepare the parameters for the callback
         */
        if (!empty($attributes)) {
            $attributes[0] = $prefixedPath;
            $parameters    = [$attributes];
        } else {
            $parameters = [$prefixedPath];
        }
        $parameters[] = $local;

        /**
         * Call the callback to generate the HTML
         */
        /** @var string $html */
        $html = call_user_func_array($callback, $parameters);

        return $html;
    }

    /**
     * @param Collection $collection
     * @param string     $completeTargetPath
     *
     * @return bool
     * @throws Exception
     */
    private function getJoin(Collection $collection, string $completeTargetPath): bool
    {
        $join = $collection->getJoin();

        /**
         * Check for valid target paths if the collection must be joined
         */
        if (true !== $join) {
            /**
             * We need a valid final target path
             */
            if (empty($completeTargetPath)) {
                throw new InvalidTargetPath($completeTargetPath);
            }

            if (is_dir($completeTargetPath)) {
                throw new TargetPathIsDirectory($completeTargetPath);
            }
        }

        return $join;
    }

    /**
     * @param Collection      $collection
     * @param string          $completeTargetPath
     * @param assets_callback $callback
     * @param string          $output
     *
     * @return string
     */
    private function getOutput(
        Collection $collection,
        string $completeTargetPath,
        array $callback,
        string $output
    ): string {
        $prefixedPath = $this->calculatePrefixedPath(
            $collection,
            $collection->getTargetUri(),
            $completeTargetPath
        );

        /**
         * Generate the HTML
         */
        $html = $this->doCallback(
            $callback,
            $collection->getAttributes(),
            $prefixedPath,
            $collection->getTargetIsLocal()
        );

        /**
         * Implicit output prints the content directly
         */
        if (true === $this->implicitOutput) {
            echo $html;
        } else {
            $output .= $html;
        }

        return $output;
    }

    /**
     * @throws Exception
     */
    private function getSourcePath(Asset $asset, string $completeSourcePath): string
    {
        $sourcePath = $asset->getRealSourcePath($completeSourcePath);

        /**
         * We need a valid source path
         */
        if (empty($sourcePath)) {
            $sourcePath = $asset->getPath();

            throw new InvalidAssetSourcePath($sourcePath);
        }
        return $sourcePath;
    }

    /**
     * @throws Exception
     */
    private function isFilterNeeded(
        Asset $asset,
        string $targetPath,
        string $sourcePath,
        bool $filterNeeded
    ): bool {
        if (true === $asset->isLocal()) {
            /**
             * Make sure the target path is not the same source path
             */
            if ($targetPath === $sourcePath) {
                throw new AssetSourceTargetCollision($targetPath);
            }

            if (true === $this->phpFileExists($targetPath)) {
                if (filemtime($targetPath) !== filemtime($sourcePath)) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return $filterNeeded;
    }

    /**
     * @param mixed $parameters
     * @param bool  $local
     *
     * @return string
     * @throws Exception
     */
    private function jsLink(mixed $parameters = [], bool $local = true): string
    {
        return $this->processParameters(
            $parameters,
            $local,
            "script",
            "application/javascript",
            "src"
        );
    }

    /**
     * Processes common parameters for js/css link generation
     */
    private function processParameters(
        mixed $parameters,
        bool $local,
        string $helperClass,
        string $type,
        string $name
    ): string {
        $params = $parameters;

        if (true !== is_array($params)) {
            $params = [$parameters, $local];
        }

        if (true === isset($params[1])) {
            $local = (bool)$params[1];
            unset($params[1]);
        } else {
            if (true === isset($params["local"])) {
                $local = (bool)$params["local"];

                unset($params["local"]);
            }
        }

        if (!isset($params["type"])) {
            $params["type"] = $type;
        }

        /**
         * Only for css
         */
        if ("link" === $helperClass) {
            $params["rel"] = "stylesheet";
        }

        if (!isset($params[$name])) {
            $params[$name] = "";
            if (isset($params[0])) {
                $params[$name] = $params[0];
                unset($params[0]);
            }
        }

        /** @var string $tag */
        $tag = $params[$name];
        unset($params[$name]);

        /**
         * URLs are generated through the "url" service when available
         */
        if (true === $local) {
            if (null !== $this->container && $this->container->has("url")) {
                /** @var Url $url */
                $url = $this->container->get("url");
                $tag = $url->getStatic($tag);
            } else {
                $tag = "/" . ltrim($tag, "/");
            }
        }

        /** @var Link|Script $helper */
        $helper = $this->tagFactory->newInstance($helperClass);

        $helper->__invoke(""); // no indentation
        /** @var assets_parameters $params */
        $helper->add($tag, $params);

        $output = (string)$helper;

        $helper->reset();

        return $output;
    }
}
