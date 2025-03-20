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

use Exception as BaseException;
use Phalcon\Assets\Asset\Css as AssetCss;
use Phalcon\Assets\Asset\Js as AssetJs;
use Phalcon\Assets\Inline\Css as InlineCss;
use Phalcon\Assets\Inline\Js as InlineJs;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Html\Helper\Link;
use Phalcon\Html\Helper\Script;
use Phalcon\Html\TagFactory;

use function call_user_func_array;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function is_array;
use function is_dir;
use function is_object;

use const PHP_EOL;

/**
 * Manages collections of CSS/JavaScript assets
 *
 * @phpstan-type TOptions = array{
 *      sourceBasePath?: string,
 *      targetBasePath?: string
 * }
 *
 * @phpstan-type TParameters = array{
 *      local?: bool,
 *      type?: string,
 *      rel?: string,
 *      string?: string,
 *      0?: string,
 *      1?: string
 * }
 */
class Manager implements InjectionAwareInterface
{
    use InjectionAwareTrait;

    /**
     * @var array<string, Collection>
     */
    protected array $collections = [];

    /**
     * @var bool
     */
    protected bool $implicitOutput = true;

    /**
     * Manager constructor.
     *
     * @param TagFactory $tagFactory
     * @param TOptions   $options
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
     *
     * @return static
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
     *
     * @return static
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
     * @param string                $path
     * @param bool                  $local
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     *
     * @return static
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
     *
     * @return static
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
     *
     * @return static
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
        $this->addInlineCodeByType(
            'css',
            new InlineCss($content, $filter, $attributes)
        );

        return $this;
    }

    /**
     * Adds an inline JavaScript to the 'js' collection
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
     * @param string                $path
     * @param bool                  $local
     * @param bool                  $filter
     * @param array<string, string> $attributes
     * @param string|null           $version
     * @param bool                  $autoVersion
     *
     * @return static
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
     *
     * @param string $name
     *
     * @return Collection
     */
    public function collection(string $name): Collection
    {
        return $this->checkAndCreateCollection($name);
    }

    /**
     * Creates/Returns a collection of assets by type
     *
     * @param AssetInterface[] $assets
     * @param string           $type
     *
     * @return AssetInterface[]
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
     * Returns a collection by its id.
     *
     * ```php
     * $scripts = $assets->get("js");
     * ```
     *
     * @param string $name
     *
     * @return Collection
     * @throws Exception
     */
    public function get(string $name): Collection
    {
        if (!isset($this->collections[$name])) {
            throw new Exception('The collection does not exist in the manager');
        }

        return $this->collections[$name];
    }

    /**
     * Returns existing collections in the manager
     *
     * @return array<string, Collection>
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    /**
     * Returns the CSS collection of assets
     *
     * @return Collection
     */
    public function getCss(): Collection
    {
        return $this->checkAndCreateCollection('css');
    }

    /**
     * Returns the CSS collection of assets
     *
     * @return Collection
     */
    public function getJs(): Collection
    {
        return $this->checkAndCreateCollection('js');
    }

    /**
     * Returns the manager options
     *
     * @return TOptions
     */
    public function getOptions(): array
    {
        return $this->options;
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
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->collections[$name]);
    }

    /**
     * Traverses a collection calling the callback to generate its HTML
     *
     * @param Collection $collection
     * @param string     $type
     *
     * @return string|null
     * @throws Exception
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
        $assets = $this->collectionAssetsByType($collection->getAssets(), $type);

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
                    throw new Exception(
                        "Asset '" . $sourcePath . "' does not have a valid target path"
                    );
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
                 * Check if the asset must be filtered
                 */
                $mustFilter = $asset->getFilter();

                /**
                 * Only filter the asset if it's marked as 'filterable'
                 */
                if (true === $mustFilter) {
                    foreach ($filters as $filter) {
                        /**
                         * Filters must be valid objects
                         */
                        if (!is_object($filter)) {
                            throw new Exception('Filter is invalid');
                        }

                        /**
                         * Calls the method 'filter' which must return a
                         * filtered version of the content
                         */
                        $filteredContent = $filter->filter($content);
                        $content         = $filteredContent;
                    }

                    /**
                     * Update the joined filtered content
                     */
                    if (true === $join) {
                        $filteredJoinedContent .= $filteredContent;
                        if ($asset->getType() !== $typeCss) {
                            $filteredJoinedContent .= ';';
                        }
                    }
                } else {
                    /**
                     * Update the joined filtered content
                     */
                    if (true === $join) {
                        $filteredJoinedContent .= $content;
                    } else {
                        $filteredContent = $content;
                    }
                }

                if (true !== $join) {
                    /**
                     * Write the file using file-put-contents. This respects the
                     * openbase-dir also writes to streams
                     */
                    file_put_contents($targetPath, $filteredContent);
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
             * Write the file using file_put_contents. This respects the
             * openbase-dir also writes to streams
             */
            file_put_contents($completeTargetPath, $filteredJoinedContent);

            $output = $this->getOutput($collection, $completeTargetPath, $callback, $output);
        }

        return $output;
    }

    /**
     * Prints the HTML for CSS assets
     *
     * @param string|null $name
     *
     * @return string|null
     * @throws Exception
     */
    public function outputCss(string | null $name = null): string | null
    {
        $collection = $this->getCss();
        if (null !== $name) {
            $collection = $this->get($name);
        }

        return $this->output($collection, 'css');
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
    public function outputInline(Collection $collection, string $type): string
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
                /** @var FilterInterface $filter */
                foreach ($filters as $filter) {
                    /**
                     * Filters must be valid objects
                     */
                    if (!is_object($filter)) {
                        throw new Exception('Filter is invalid');
                    }

                    /**
                     * Calls the method 'filter' which must return a filtered
                     * version of the content
                     */
                    $content = $filter->filter($content);
                }

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
     *
     * @param string|null $name
     *
     * @return string
     * @throws Exception
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
     *
     * @param string|null $name
     *
     * @return string
     * @throws Exception
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
     *
     * @param string|null $name
     *
     * @return string|null
     * @throws Exception
     */
    public function outputJs(string | null $name = null): string | null
    {
        $collection = $this->getJs();
        if (!empty($name)) {
            $collection = $this->get($name);
        }

        return $this->output($collection, 'js');
    }

    /**
     * Sets a collection in the Assets Manager
     *
     *```php
     * $assets->set("js", $collection);
     *```
     *
     * @param string     $name
     * @param Collection $collection
     *
     * @return static
     */
    public function set(string $name, Collection $collection): static
    {
        $this->collections[$name] = $collection;

        return $this;
    }

    /**
     * Sets the manager options
     *
     * @param TOptions $options
     *
     * $return static
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets if the HTML generated must be directly printed or returned
     *
     * @param bool $implicitOutput
     *
     * $return static
     */
    public function useImplicitOutput(bool $implicitOutput): static
    {
        $this->implicitOutput = $implicitOutput;

        return $this;
    }

    /**
     * Calculates the prefixed path including the version
     *
     * @param Collection $collection
     * @param string     $path
     * @param string     $filePath
     *
     * @return string
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

    /**
     * @param string $type
     *
     * @return Collection
     */
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
     * @param mixed $parameters
     * @param bool  $local
     *
     * @return string
     * @throws BaseException
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
     * @param array<Manager|string> $callback
     * @param array<string, string> $attributes
     * @param string                $prefixedPath
     * @param bool                  $local
     *
     * @return string
     */
    private function doCallback(
        array $callback,
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
        return call_user_func_array($callback, $parameters);
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
                throw new Exception(
                    "Path '" . $completeTargetPath . "' is not a valid target path (1)"
                );
            }

            if (is_dir($completeTargetPath)) {
                throw new Exception(
                    "Path '" . $completeTargetPath . "' is not a valid target path (2), it is a directory."
                );
            }
        }

        return $join;
    }

    /**
     * @param Collection                       $collection
     * @param string                           $completeTargetPath
     * @param array<array-key, Manager|string> $callback
     * @param string                           $output
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
     * @param Asset  $asset
     * @param string $completeSourcePath
     *
     * @return string
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

            throw new Exception(
                "Asset '" . $sourcePath . "' does not have a valid source path"
            );
        }
        return $sourcePath;
    }

    /**
     * @param Asset  $asset
     * @param string $targetPath
     * @param string $sourcePath
     * @param bool   $filterNeeded
     *
     * @return bool
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
                throw new Exception(
                    "Asset '" . $targetPath . "' have the same source and target paths"
                );
            }

            if (true === file_exists($targetPath)) {
                if (filemtime($targetPath) !== filemtime($sourcePath)) {
                    $filterNeeded = true;
                }
            } else {
                $filterNeeded = true;
            }
        }

        return $filterNeeded;
    }

    /**
     * @param mixed $parameters
     * @param bool  $local
     *
     * @return string
     * @throws BaseException
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
     *
     * @param mixed  $parameters
     * @param bool   $local
     * @param string $helperClass
     * @param string $type
     * @param string $name
     *
     * @return string
     * @throws BaseException
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
         * URLs are generated through the "url" service
         */
        if (true === $local) {
            $tag = "/" . ltrim($tag, "/");
        }

        /** @var Link|Script $helper */
        $helper = $this->tagFactory->newInstance($helperClass);

        $helper->__invoke(""); // no indentation
        $helper->add($tag, $params);

        return (string)$helper;
    }
}
