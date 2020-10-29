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

use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Tag;
use Phalcon\Assets\Asset\Js as AssetJs;
use Phalcon\Assets\Asset\Css as AssetCss;
use Phalcon\Assets\Inline\Css as InlineCss;
use Phalcon\Assets\Inline\Js as InlineJs;
use Phalcon\Di\DiInterface;
use Phalcon\Di\AbstractInjectionAware;

/**
 * Phalcon\Assets\Manager
 *
 * Manages collections of CSS/JavaScript assets
 *
 * @property array $collections
 * @property array $options
 * @property bool  $implicitOutput
 */
class Manager
{
    use InjectionAwareTrait;

    /**
     * @var array
     */
    protected array $collections = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var bool
     */
    protected bool $implicitOutput = true;

    /**
     * Manager constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Adds a raw asset to the manager
     *
     *```php
     * $assets->addAsset(
     *     new Phalcon\Assets\Asset("css", "css/style.css")
     * );
     *```
     *
     * @param Asset $asset
     *
     * @return $this
     */
    public function addAsset(Asset $asset): Manager
    {
        /**
         * Adds the asset by its type
         */
        $this->addAssetByType($asset->getType(), $asset);

        return $this;
    }

    /**
     * Adds a asset by its type
     *
     *```php
     * $assets->addAssetByType(
     *     "css",
     *     new \Phalcon\Assets\Asset\Css("css/style.css")
     * );
     *```
     *
     * @param string $type
     * @param Asset  $asset
     *
     * @return $this
     */
    public function addAssetByType(string $type, Asset $asset): Manager
    {
        $collection = $this->checkAndCreateCollection($type);
        $collection->add($asset);

        return $this;
    }

    /**
    * Adds a CSS asset to the 'css' collection
    *
    *```php
    * $assets->addCss("css/bootstrap.css");
    * $assets->addCss("http://bootstrap.my-cdn.com/style.css", false);
    *```
    *
     * @param string      $path
     * @param bool        $local
     * @param bool        $filter
     * @param array|null  $attributes
     * @param string|null $version
     * @param bool        $autoVersion
     *
     * @return $this
     */
    public function addCss(
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = null,
        string $version = null,
        bool $autoVersion = false
    ): Manager {
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
     * @return $this
     */
    public function addInlineCode(Inline $code): Manager
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
     * @return $this
     */
    public function addInlineCodeByType(string $type, Inline $code): Manager
    {
        $collection = $this->checkAndCreateCollection($type);
        $collection->addInline($code);

        return $this;
    }

    /**
     * Adds an inline CSS to the 'css' collection
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
    ): Manager {
        $this->addInlineCodeByType(
            'css',
            new InlineCss($content, $filter, $attributes)
        );

        return $this;
    }

    /**
     * Adds an inline JavaScript to the 'js' collection
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
    ): Manager {
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
     * $assets->addJs("http://jquery.my-cdn.com/jquery.js", false);
     *```
     *
     * @param string      $path
     * @param bool        $local
     * @param bool        $filter
     * @param array       $attributes
     * @param string|null $version
     * @param bool        $autoVersion
     *
     * @return $this
     */
    public function addJs(
        string $path,
        bool $local = true,
        bool $filter = true,
        array $attributes = [],
        string $version = null,
        bool $autoVersion = false
    ): Manager {
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
     * @param array  $assets
     * @param string $type
     *
     * @return array
     */
    public function collectionAssetsByType(array $assets, string $type): array
    {
        $filtered = [];
        foreach ($assets as $asset) {
            if ($asset instanceof AssetInterface &&
                $type === $asset->getType()) {
                $filtered[] = $asset;
            }
        }

        return $filtered;
    }

    /**
     * Returns true or false if collection exists.
     *
     * ```php
     * if ($assets->exists("jsHeader")) {
     *     // \Phalcon\Assets\Collection
     *     $collection = $assets->get("jsHeader");
     * }
     * ```
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists(string $name): bool
    {
        return isset($this->collections[$name]);
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
        if (true !== isset($this->collections[$name])) {
            throw new Exception('The collection does not exist in the manager');
        }

        return $this->collections[$name];
    }

    /**
     * Returns existing collections in the manager
     *
     * @return Collection[]
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
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Traverses a collection calling the callback to generate its HTML
     *
     * @param callback callback
     * @param string type
     */
    public function output(
        Collection $collection,
        callable $callback,
        string $type
    ): ?string {
//        string output;
//        var asset, assets, attributes, autoVersion, collectionSourcePath,
//            collectionTargetPath, completeSourcePath, completeTargetPath,
//            content, filter, filters, filteredContent, filteredJoinedContent,
//            filterNeeded, html, join, local, modificationTime, mustFilter,
//            options, parameters, path, prefixedPath, sourceBasePath = null,
//            sourcePath,  targetBasePath = null, targetPath, targetUri, typeCss,
//            useImplicitOutput, version;
//
//        let useImplicitOutput = this->implicitOutput,
//            output            = "";
//
//        /**
//         * Get the assets as an array
//         */
//        let assets = this->collectionAssetsByType(
//            collection->getAssets(),
//            type
//        );
//
//        /**
//         * Get filters in the collection
//         */
//        let filters = collection->getFilters(),
//            typeCss = "css";
//
//        /**
//         * Prepare options if the collection must be filtered
//         */
//        if count(filters) {
//            let options = this->options;
//
//            /**
//             * Check for global options in the assets manager
//             */
//            if typeof options == "array" {
//                /**
//                 * The source base path is a global location where all assets
//                 * are located
//                 */
//                fetch sourceBasePath, options["sourceBasePath"];
//
//                /**
//                 * The target base path is a global location where all assets
//                 * are written
//                 */
//                fetch targetBasePath, options["targetBasePath"];
//            }
//
//            /**
//             * Check if the collection have its own source base path
//             */
//            let collectionSourcePath = collection->getSourcePath();
//
//            /**
//             * Concatenate the global base source path with the collection one
//             */
//            if collectionSourcePath {
//                let completeSourcePath = sourceBasePath . collectionSourcePath;
//            } else {
//                let completeSourcePath = sourceBasePath;
//            }
//
//            /**
//             * Check if the collection have its own target base path
//             */
//            let collectionTargetPath = collection->getTargetPath();
//
//            /**
//             * Concatenate the global base source path with the collection one
//             */
//            if collectionTargetPath {
//                let completeTargetPath = targetBasePath . collectionTargetPath;
//            } else {
//                let completeTargetPath = targetBasePath;
//            }
//
//            /**
//             * Global filtered content
//             */
//            let filteredJoinedContent = "";
//
//            /**
//             * Check if the collection have its own target base path
//             */
//            let join = collection->getJoin();
//
//            /**
//             * Check for valid target paths if the collection must be joined
//             */
//            if join {
//                /**
//                * We need a valid final target path
//                */
//                if unlikely !completeTargetPath {
//                    throw new Exception(
//                        "Path '" . completeTargetPath . "' is not a valid target path (1)"
//                    );
//                }
//
//                if unlikely is_dir(completeTargetPath) {
//                    throw new Exception(
//                        "Path '" . completeTargetPath . "' is not a valid target path (2), is dir."
//                    );
//                }
//            }
//        }
//
//        /**
//         * walk in assets
//         */
//        for asset in assets {
//            let filterNeeded = false,
//                type         = asset->getType();
//
//            /**
//             * Is the asset local?
//             */
//            let local = asset->getLocal();
//
//            /**
//             * If the collection must not be joined we must print a HTML for
//             * each one
//             */
//            if count(filters) {
//                if local {
//                    /**
//                     * Get the complete path
//                     */
//                    let sourcePath = asset->getRealSourcePath(
//                        completeSourcePath
//                    );
//
//                    /**
//                     * We need a valid source path
//                     */
//                    if unlikely !sourcePath {
//                        let sourcePath = asset->getPath();
//
//                        throw new Exception(
//                            "Asset '" . sourcePath . "' does not have a valid source path"
//                        );
//                    }
//                } else {
//                    /**
//                     * Get the complete source path
//                     */
//                    let sourcePath = asset->getPath();
//
//                    /**
//                     * assets paths are always filtered
//                     */
//                    let filterNeeded = true;
//                }
//
//                /**
//                 * Get the target path, we need to write the filtered content to
//                 * a file
//                 */
//                let targetPath = asset->getRealTargetPath(completeTargetPath);
//
//                /**
//                 * We need a valid final target path
//                 */
//                if unlikely !targetPath {
//                    throw new Exception(
//                        "Asset '" . sourcePath . "' does not have a valid target path"
//                    );
//                }
//
//                if local {
//                    /**
//                     * Make sure the target path is not the same source path
//                     */
//                    if unlikely targetPath == sourcePath {
//                        throw new Exception(
//                            "Asset '" . targetPath . "' have the same source and target paths"
//                        );
//                    }
//
//                    if file_exists(targetPath) {
//                        if compare_mtime(targetPath, sourcePath) {
//                            let filterNeeded = true;
//                        }
//                    } else {
//                        let filterNeeded = true;
//                    }
//                }
//            } else {
//                /**
//                 * If there are not filters, just print/buffer the HTML
//                 */
//                let path         = asset->getRealTargetUri(),
//                    prefixedPath = this->getPrefixedPath(collection, path);
//
//                if null === asset->getVersion() && asset->isAutoVersion() {
//					let version     = collection->getVersion(),
//					    autoVersion = collection->isAutoVersion();
//
//				    if autoVersion && local {
//				        let modificationTime = filemtime(asset->getRealSourcePath()),
//				            version          = version ? version . "." . modificationTime : modificationTime;
//				    }
//
//					if version {
//						let prefixedPath = prefixedPath . "?ver=" . version;
//					}
//				}
//
//                /**
//                 * Gets extra HTML attributes in the asset
//                 */
//                let attributes = asset->getAttributes();
//
//                /**
//                 * Prepare the parameters for the callback
//                 */
//                if typeof attributes == "array" {
//                    let attributes[0] = prefixedPath;
//                    let parameters = [attributes];
//                } else {
//                    let parameters = [prefixedPath];
//                }
//                let parameters[] = local;
//
//                /**
//                 * Call the callback to generate the HTML
//                 */
//                let html = call_user_func_array(callback, parameters);
//
//                /**
//                 * Implicit output prints the content directly
//                 */
//                if useImplicitOutput == true {
//                    echo html;
//                } else {
//                    let output .= html;
//                }
//
//                continue;
//            }
//
//            if filterNeeded == true {
//                /**
//                 * Gets the asset's content
//                 */
//                let content = asset->getContent(completeSourcePath);
//
//                /**
//                 * Check if the asset must be filtered
//                 */
//                let mustFilter = asset->getFilter();
//
//                /**
//                 * Only filter the asset if it's marked as 'filterable'
//                 */
//                if mustFilter == true {
//                    for filter in filters {
//                        /**
//                         * Filters must be valid objects
//                         */
//                        if unlikely typeof filter != "object" {
//                            throw new Exception("Filter is invalid");
//                        }
//
//                        /**
//                         * Calls the method 'filter' which must return a
//                         * filtered version of the content
//                         */
//                        let filteredContent = filter->filter(content),
//                            content         = filteredContent;
//                    }
//
//                    /**
//                     * Update the joined filtered content
//                     */
//                    if join == true {
//                        if type == typeCss {
//                            let filteredJoinedContent .= filteredContent;
//                        } else {
//                            let filteredJoinedContent .= filteredContent . ";";
//                        }
//                    }
//                } else {
//                    /**
//                     * Update the joined filtered content
//                     */
//                    if join == true {
//                        let filteredJoinedContent .= content;
//                    } else {
//                        let filteredContent = content;
//                    }
//                }
//
//                if !join {
//                    /**
//                     * Write the file using file-put-contents. This respects the
//                     * openbase-dir also writes to streams
//                     */
//                    file_put_contents(targetPath, filteredContent);
//                }
//            }
//
//            if !join {
//                /**
//                 * Generate the HTML using the original path in the asset
//                 */
//                let path         = asset->getRealTargetUri(),
//                    prefixedPath = this->getPrefixedPath(collection, path);
//
//                if null === asset->getVersion() && asset->isAutoVersion() {
//					let version     = collection->getVersion(),
//					    autoVersion = collection->isAutoVersion();
//
//				    if autoVersion && local {
//				        let modificationTime = filemtime(asset->getRealSourcePath()),
//				            version          = version ? version . "." . modificationTime : modificationTime;
//				    }
//
//					if version {
//						let prefixedPath = prefixedPath . "?ver=" . version;
//					}
//				}
//
//                /**
//                 * Gets extra HTML attributes in the asset
//                 */
//                let attributes = asset->getAttributes();
//
//                /**
//                 * Filtered assets are always local
//                 */
//                let local = true;
//
//                /**
//                 * Prepare the parameters for the callback
//                 */
//                if typeof attributes == "array" {
//                    let attributes[0] = prefixedPath;
//                    let parameters = [attributes];
//                } else {
//                    let parameters = [prefixedPath];
//                }
//                let parameters[] = local;
//
//                /**
//                * Call the callback to generate the HTML
//                */
//                let html = call_user_func_array(callback, parameters);
//
//                /**
//                * Implicit output prints the content directly
//                */
//                if useImplicitOutput == true {
//                    echo html;
//                } else {
//                    let output .= html;
//                }
//            }
//        }
//
//        if count(filters) {
//            if join == true {
//                /**
//                 * Write the file using file_put_contents. This respects the
//                 * openbase-dir also writes to streams
//                 */
//                file_put_contents(completeTargetPath, filteredJoinedContent);
//
//                /**
//                 * Generate the HTML using the original path in the asset
//                 */
//                let targetUri    = collection->getTargetUri(),
//                    prefixedPath = this->getPrefixedPath(collection, targetUri),
//                    version      = collection->getVersion(),
//                    autoVersion  = collection->isAutoVersion();
//
//                if autoVersion && local {
//                    let modificationTime = filemtime(completeTargetPath),
//                        version          = version ? version . "." . modificationTime : modificationTime;
//                }
//
//                if version {
//                    let prefixedPath = prefixedPath . "?ver=" . version;
//                }
//
//                /**
//                 * Gets extra HTML attributes in the collection
//                 */
//                let attributes = collection->getAttributes();
//
//                /**
//                 *  Gets local
//                 */
//                let local = collection->getTargetLocal();
//
//                /**
//                 * Prepare the parameters for the callback
//                 */
//                if typeof attributes == "array" {
//                    let attributes[0] = prefixedPath,
//                        parameters = [attributes];
//                } else {
//                    let parameters = [prefixedPath];
//                }
//                let parameters[] = local;
//
//                /**
//                 * Call the callback to generate the HTML
//                 */
//                let html = call_user_func_array(callback, parameters);
//
//                /**
//                 * Implicit output prints the content directly
//                 */
//                if useImplicitOutput == true {
//                    echo html;
//                } else {
//                    let output .= html;
//                }
//            }
//        }
//
//        return output;
    }

    /**
     * Prints the HTML for CSS assets
     *
     * @param string|null $name
     *
     * @return string
     */
    public function outputCss(string $name = null): string
    {
//        array callback;
//        var collection, container, tag;
//
//        if !collectionName {
//            let collection = this->getCss();
//        } else {
//            let collection = this->get(collectionName);
//        }
//
//        let callback  = ["Phalcon\\Tag", "stylesheetLink"],
//            container = this->container;
//
//        if typeof container == "object" && container->has("tag") {
//            let tag      = container->getShared("tag"),
//                callback = [tag, "stylesheetLink"];
//        }
//
//        return $this->output(collection, callback, "css");
    }

    /**
     * Traverses a collection and generate its HTML
     *
     * @param Collection $collection
     * @param string     $type
     *
     * @return string
     */
    public function outputInline(Collection $collection, string $type): string
    {
        let output        = "",
            html          = "",
            joinedContent = "",
            codes         = collection->getCodes(),
            filters       = collection->getFilters(),
            join          = collection->getJoin() ;

        if count(codes) {
            for code in codes {
                let attributes = code->getAttributes(),
                    content = code->getContent();

                for filter in filters {
                    /**
                     * Filters must be valid objects
                     */
                    if unlikely typeof filter != "object" {
                        throw new Exception("Filter is invalid");
                    }

                    /**
                     * Calls the method 'filter' which must return a filtered
                     * version of the content
                     */
                    let content = filter->filter(content);
                }

                if join {
                    let joinedContent .= content;
                } else {
                    let html .= Tag::tagHtml(type, attributes, false, true)
                              . content
                              . Tag::tagHtmlClose(type, true);
                }
            }

            if join {
                let html .= Tag::tagHtml(type, attributes, false, true)
                          . joinedContent
                          . Tag::tagHtmlClose(type, true);
            }

            /**
             * Implicit output prints the content directly
             */
            if this->implicitOutput == true {
                echo html;
            } else {
                let output .= html;
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
     */
    public function outputInlineCss(string $name = null): string
    {
        $collection = $this->getCss();
        if (true !== empty($name)) {
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
     */
    public function outputInlineJs(string $name = null): string
    {
        $collection = $this->getJs();
        if (true !== empty($name)) {
            $collection = $this->get($name);
        }

        return $this->outputInline($collection, 'script');
    }

    /**
     * Prints the HTML for JS assets
     *
     * @param string|null $name
     *
     * @return string
     */
    public function outputJs(string $name = null): string
    {
        $collection = $this->getJs();
        if (true !== empty($name)) {
            $collection = $this->get($name);
        }

//        $callback = ["Phalcon\\Tag", "javascriptInclude"];
//
//        let container = this->container;
//        if typeof container == "object" && container->has("tag") {
//            let tag      = container->getShared("tag"),
//                callback = [tag, "javascriptInclude"];
//        }

        return $this->output(collection, callback, "js");
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
     * @return $this
     */
    public function set(string $name, Collection $collection): Manager
    {
        $this->collections[$name] = $collection;

        return $this;
    }

    /**
     * Sets the manager options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): Manager
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets if the HTML generated must be directly printed or returned
     *
     * @param bool $implicitOutput
     *
     * @return $this
     */
    public function useImplicitOutput(bool $implicitOutput): Manager
    {
        $this->implicitOutput = $implicitOutput;

        return $this;
    }

    /**
     * Returns the prefixed path
     *
     * @param Collection $collection
     * @param string     $path
     *
     * @return string
     */
    private function getPrefixedPath(Collection $collection, string $path): string
    {
        return $collection->getPrefix() . $path;
    }

    private function checkAndCreateCollection(string $type): Collection
    {
        if (true !== isset($this->collections[$type])) {
            $this->collections[$type] = new Collection();
        }

        return $this->collections[$type];
    }
}
