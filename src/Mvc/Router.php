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

namespace Phalcon\Mvc;

use Closure;
use Phalcon\Cache\Adapter\AdapterInterface as CacheAdapterInterface;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Http\RequestInterface;
use Phalcon\Mvc\Router\Exception;
use Phalcon\Mvc\Router\Exceptions\BeforeMatchNotCallable;
use Phalcon\Mvc\Router\Exceptions\ConfigKeyMustBeArray;
use Phalcon\Mvc\Router\Exceptions\EmptyGroupOfRoutes;
use Phalcon\Mvc\Router\Exceptions\GroupRoutesMustBeArray;
use Phalcon\Mvc\Router\Exceptions\InvalidConfigSource;
use Phalcon\Mvc\Router\Exceptions\InvalidRoutePosition;
use Phalcon\Mvc\Router\Exceptions\MissingGroupRouteKey;
use Phalcon\Mvc\Router\Exceptions\MissingRouteConfigKey;
use Phalcon\Mvc\Router\Exceptions\UnknownHttpMethod;
use Phalcon\Mvc\Router\Exceptions\WrongPathsKey;
use Phalcon\Mvc\Router\Group;
use Phalcon\Mvc\Router\GroupInterface;
use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\Router\RouteInterface;

use function array_merge;
use function array_reverse;
use function explode;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;
use function preg_match;
use function rtrim;
use function strtolower;
use function trim;
use function ucfirst;

/**
 * Phalcon\Mvc\Router is the standard framework router. Routing is the
 * process of taking a URI endpoint (that part of the URI which comes after the
 * base URL) and decomposing it into parameters to determine which module,
 * controller, and action of that controller should receive the request
 *
 * ```php
 * use Phalcon\Mvc\Router;
 *
 * $router = new Router();
 *
 * $router->add(
 *     "/documentation/{chapter}/{name}\.{type:[a-z]+}",
 *     [
 *         "controller" => "documentation",
 *         "action"     => "show",
 *     ]
 * );
 *
 * $router->handle(
 *     "/documentation/1/examples.html"
 * );
 *
 * echo $router->getControllerName();
 * ```
 *
 * @phpstan-import-type mvc_router_defaults from MvcTypes
 * @phpstan-import-type mvc_router_http_methods from MvcTypes
 * @phpstan-import-type mvc_router_config_group from MvcTypes
 * @phpstan-import-type mvc_router_config_route from MvcTypes
 * @phpstan-import-type mvc_router_dump from MvcTypes
 * @phpstan-import-type mvc_router_dumped_route from MvcTypes
 * @phpstan-import-type mvc_router_hostname_buckets from MvcTypes
 * @phpstan-import-type mvc_router_index_buckets from MvcTypes
 * @phpstan-import-type mvc_router_matches from MvcTypes
 * @phpstan-import-type mvc_router_method_buckets from MvcTypes
 * @phpstan-import-type mvc_router_params from MvcTypes
 * @phpstan-import-type mvc_router_paths from MvcTypes
 * @phpstan-import-type mvc_router_regex_chunks from MvcTypes
 * @phpstan-import-type mvc_router_regex_disabled from MvcTypes
 * @phpstan-import-type mvc_router_regex_mark_map from MvcTypes
 * @phpstan-import-type mvc_router_route_meta from MvcTypes
 * @phpstan-import-type mvc_router_shadow_buckets from MvcTypes
 * @phpstan-import-type mvc_router_static_buckets from MvcTypes
 */
class Router extends AbstractInjectionAware implements RouterInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    public const POSITION_FIRST = 0;
    public const POSITION_LAST  = 1;

    /**
     * Number of alternatives per combined-regex chunk. Empirically derived
     * (FastRoute uses ~10) - keeps each chunk below PCRE's optimizer cliff.
     */
    public const REGEX_CHUNK_SIZE = 10;

    public const URI_SOURCE_GET_URL            = 0;
    public const URI_SOURCE_SERVER_REQUEST_URI = 1;

    protected string $action = "";

    /**
     * Pre-merged per-method candidate buckets in attach order. For each HTTP
     * method seen on any registered route, the bucket contains the
     * method-specific routes followed by the "*" (no-constraint) routes.
     * The "*" key itself holds only the no-constraint routes - used when the
     * request method has no specific bucket.
     *
     * @phpstan-var mvc_router_method_buckets
     */
    protected array $candidatesByMethod = [];

    /**
     * Combined PCRE pattern per method bucket (chunked list of strings).
     * Each chunk uses (?|...) branch reset and (*:N) mark labels. Built
     * only when the bucket has no hostname routes and all patterns are
     * the standard `#^...$#u` shape.
     *
     * @phpstan-var mvc_router_regex_chunks
     */
    protected array $combinedRegexByMethod = [];

    /**
     * Boolean per method bucket: true when the combined regex cannot be
     * built.
     *
     * @phpstan-var mvc_router_regex_disabled
     */
    protected array $combinedRegexDisabled = [];

    /**
     * Map from MARK label back to the route index in
     * candidatesByMethod[method]. One per chunk.
     *
     *   combinedRegexMarkMap[method][chunkIdx][markLabel] = routeIdx
     *
     * @phpstan-var mvc_router_regex_mark_map
     */
    protected array $combinedRegexMarkMap = [];

    protected string $controller = "";

    protected string $defaultAction = "";

    protected string $defaultController = "";

    protected string $defaultModule = "";

    protected string $defaultNamespace = "";

    /**
     * @phpstan-var mvc_router_params
     */
    protected array $defaultParams = [];

    /**
     * Per-method buckets of routes with hostname constraints, grouped by
     * raw hostname string. Routes are referenced by their integer index
     * into candidatesByMethod[method].
     *
     * @phpstan-var mvc_router_hostname_buckets
     */
    protected array $hostnameByMethod = [];

    /**
     * Per-method indices of routes without a hostname constraint, in
     * attach order.
     *
     * @phpstan-var mvc_router_index_buckets
     */
    protected array $hostnameLessByMethod = [];

    /**
     * @phpstan-var array<array-key, int|string>
     */
    protected array $keyRouteIds = [];

    /**
     * @phpstan-var array<string, int|string>
     */
    protected array $keyRouteNames = [];

    protected RouteInterface | null $matchedRoute = null;

    /**
     * @phpstan-var mvc_router_matches
     */
    protected array $matches = [];

    /**
     * @phpstan-var mvc_router_method_buckets
     */
    protected array $methodRoutes = [];

    protected bool $methodRoutesDirty = true;

    protected string $module = "";

    protected string $namespaceName = "";

    /**
     * @phpstan-var mvc_router_paths|string|null
     */
    protected array | string | null $notFoundPaths = null;

    /**
     * @phpstan-var mvc_router_params
     */
    protected array $params = [];

    /**
     * Lazy-write cache target set by useCache(). When non-null, handle()
     * writes buildDispatcherDump() to this cache after a successful
     * rebuild on cache miss, then clears the property to skip subsequent
     * writes.
     */
    protected CacheAdapterInterface | null $pendingCache = null;

    protected string $pendingCacheKey = "";

    protected bool $removeExtraSlashes = false;

    /**
     * Single-source per-route metadata cache. One entry per route, keyed
     * by the route's intrinsic id. Replaces the previous per-method-bucket
     * replication of metadata arrays. Built once in rebuildMethodIndex().
     *
     * Shape: routeMeta[routeId] = [
     *     "pattern":     string,
     *     "isRegex":     bool,
     *     "hostname":    string|null,
     *     "hostRegex":   string|null,
     *     "beforeMatch": callable|null
     *   ]
     *
     * @phpstan-var array<string, mvc_router_route_meta>
     */
    protected array $routeMeta = [];

    /**
     * @phpstan-var list<RouteInterface>
     */
    protected array $routes = [];

    /**
     * Static-route hash, populated by rebuildMethodIndex(). For each method
     * bucket (including "*"), maps URI => list of routes whose compiled
     * pattern is a literal string equal to that URI.
     *
     * @phpstan-var mvc_router_static_buckets
     */
    protected array $staticByMethod = [];

    /**
     * Shadow-detection map. If staticShadowedByMethod[method][uri] is set,
     * the static URI in that bucket is shadowed by a later-attached regex
     * route - the fast path MUST NOT be used; fall through to the dynamic
     * loop so the regex wins (reverse-iteration semantics).
     *
     * @phpstan-var mvc_router_shadow_buckets
     */
    protected array $staticShadowedByMethod = [];

    protected int $uriSource = self::URI_SOURCE_GET_URL;

    protected bool $wasMatched = false;

    /**
     * Phalcon\Mvc\Router constructor
     *
     * @throws Exception
     */
    public function __construct(bool $defaultRoutes = true)
    {
        if ($defaultRoutes) {
            /**
             * Two routes are added by default to match /:controller/:action and
             * /:controller/:action/:params
             */
            $this->attach(
                new Route(
                    "#^/([\\w0-9\\_\\-]+)[/]{0,1}$#u",
                    [
                        "controller" => 1,
                    ]
                )
            );

            $this->attach(
                new Route(
                    "#^/([\\w0-9\\_\\-]+)/([\\w0-9\\.\\_]+)(/.*)?$#u",
                    [
                        "controller" => 1,
                        "action"     => 2,
                        "params"     => 3,
                    ]
                )
            );
        }
    }

    /**
     * Adds a route to the router without any HTTP constraint
     *
     *```php
     * use Phalcon\Mvc\Router;
     *
     * $router->add("/about", "About::index");
     *
     * $router->add(
     *     "/about",
     *     "About::index",
     *     ["GET", "POST"]
     * );
     *
     * $router->add(
     *     "/about",
     *     "About::index",
     *     ["GET", "POST"],
     *     Router::POSITION_FIRST
     * );
     *```
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function add(
        string $pattern,
        mixed $paths = null,
        mixed $httpMethods = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        /**
         * Every route is internally stored as a Phalcon\Mvc\Router\Route
         */
        $route = new Route($pattern, $paths, $httpMethods);

        $this->attach($route, $position);

        return $route;
    }

    /**
     * Adds a route to the router that only match if the HTTP method is CONNECT
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addConnect(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "CONNECT", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is DELETE
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addDelete(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "DELETE", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is GET
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addGet(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "GET", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is HEAD
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addHead(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "HEAD", $position);
    }

    /**
     * Add a route to the router that only match if the HTTP method is OPTIONS
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addOptions(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "OPTIONS", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PATCH
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addPatch(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PATCH", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is POST
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addPost(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "POST", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PURGE
     * (Squid and Varnish support)
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addPurge(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PURGE", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is PUT
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addPut(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "PUT", $position);
    }

    /**
     * Adds a route to the router that only match if the HTTP method is TRACE
     *
     * @param mixed $paths = [
     *                     'module => '',
     *                     'controller' => '',
     *                     'action' => '',
     *                     'namespace' => ''
     *                     ]
     *
     * @throws Exception
     */
    public function addTrace(
        string $pattern,
        mixed $paths = null,
        int $position = Router::POSITION_LAST
    ): RouteInterface {
        return $this->add($pattern, $paths, "TRACE", $position);
    }

    /**
     * Attach Route object to the routes stack.
     *
     * ```php
     * use Phalcon\Mvc\Router;
     * use Phalcon\Mvc\Router\Route;
     *
     * class CustomRoute extends Route {
     *      // ...
     * }
     *
     * $router = new Router();
     *
     * $router->attach(
     *     new CustomRoute("/about", "About::index", ["GET", "HEAD"]),
     *     Router::POSITION_FIRST
     * );
     * ```
     *
     * @return RouterInterface
     * @throws Exception
     *
     * @phpstan-return static
     */
    public function attach(
        RouteInterface $route,
        int $position = Router::POSITION_LAST
    ): static {
        switch ($position) {
            case self::POSITION_LAST:
                $this->routes[] = $route;
                break;
            case self::POSITION_FIRST:
                $this->routes = array_merge([$route], $this->routes);
                break;
            default:
                throw new InvalidRoutePosition();
        }

        $this->methodRoutesDirty = true;

        return $this;
    }

    /**
     * Produces a pure-data array describing every piece of state needed
     * to reconstruct this router. The returned array is var_export-able
     * (no objects, no closures). Used by dumpDispatcher() and by
     * Phalcon\Cache integration via useCache().
     *
     * Throws when a route has a Closure beforeMatch or converter - those
     * cannot be cached.
     *
     * @throws Exception
     *
     * @phpstan-return mvc_router_dump
     */
    public function buildDispatcherDump(): array
    {
        if ($this->methodRoutesDirty) {
            $this->rebuildMethodIndex();
        }

        $dumpedRoutes = [];
        $routeToIdx   = [];

        foreach ($this->routes as $scalarIdx => $route) {
            $routeToIdx[spl_object_id($route)] = $scalarIdx;

            $cb = $route->getBeforeMatch();
            if ($cb !== null && $cb instanceof Closure) {
                throw new Exception(
                    "Cannot cache router: route id '" . $route->getRouteId()
                    . "' has a Closure beforeMatch - only string/array callables are cacheable"
                );
            }

            $converters = $route->getConverters();
            if (is_array($converters)) {
                foreach ($converters as $convName => $converter) {
                    if ($converter instanceof Closure) {
                        throw new Exception(
                            "Cannot cache router: route id '" . $route->getRouteId()
                            . "' has a Closure converter for '" . $convName
                            . "' - only string/array callables are cacheable"
                        );
                    }
                }
            }

            $dumpedRoutes[] = [
                "class"       => get_class($route),
                "pattern"     => $route->getPattern(),
                "paths"       => $route->getPaths(),
                "methods"     => $route->getHttpMethods(),
                "hostname"    => $route->getHostname(),
                "name"        => $route->getName(),
                "id"          => $route->getRouteId(),
                "beforeMatch" => $cb,
                "converters"  => $converters,
            ];
        }

        $methodRoutesScalar = [];
        foreach ($this->methodRoutes as $innerKey => $innerVal) {
            $mostInnerArr = [];
            foreach ($innerVal as $scalarVal) {
                $mostInnerArr[] = $routeToIdx[spl_object_id($scalarVal)];
            }
            $methodRoutesScalar[$innerKey] = $mostInnerArr;
        }

        $candidatesScalar = [];
        foreach ($this->candidatesByMethod as $innerKey => $innerVal) {
            $mostInnerArr = [];
            foreach ($innerVal as $scalarVal) {
                $mostInnerArr[] = $routeToIdx[spl_object_id($scalarVal)];
            }
            $candidatesScalar[$innerKey] = $mostInnerArr;
        }

        $staticScalar = [];
        foreach ($this->staticByMethod as $innerKey => $innerVal) {
            $staticScalar[$innerKey] = [];
            foreach ($innerVal as $scalarSubKey => $mostInnerVal) {
                $mostInnerArr = [];
                foreach ($mostInnerVal as $scalarVal) {
                    $mostInnerArr[] = $routeToIdx[spl_object_id($scalarVal)];
                }
                $staticScalar[$innerKey][$scalarSubKey] = $mostInnerArr;
            }
        }

        return [
            "version"                => 1,
            "routes"                 => $dumpedRoutes,
            "methodRoutes"           => $methodRoutesScalar,
            "candidatesByMethod"     => $candidatesScalar,
            "staticByMethod"         => $staticScalar,
            "staticShadowedByMethod" => $this->staticShadowedByMethod,
            "hostnameByMethod"       => $this->hostnameByMethod,
            "hostnameLessByMethod"   => $this->hostnameLessByMethod,
            "combinedRegexByMethod"  => $this->combinedRegexByMethod,
            "combinedRegexDisabled"  => $this->combinedRegexDisabled,
            "combinedRegexMarkMap"   => $this->combinedRegexMarkMap,
            "routeMeta"              => $this->routeMeta,
        ];
    }

    /**
     * Removes all the pre-defined routes
     */
    public function clear(): void
    {
        $this->routes                 = [];
        $this->methodRoutes           = [];
        $this->candidatesByMethod     = [];
        $this->routeMeta              = [];
        $this->staticByMethod         = [];
        $this->staticShadowedByMethod = [];
        $this->hostnameByMethod       = [];
        $this->hostnameLessByMethod   = [];
        $this->combinedRegexByMethod  = [];
        $this->combinedRegexDisabled  = [];
        $this->combinedRegexMarkMap   = [];
        $this->methodRoutesDirty      = true;
    }

    /**
     * File-shaped helper around buildDispatcherDump(). Writes the dump as
     * a `<?php return [...];` file, atomically (temp + rename) so concurrent
     * dumps don't corrupt the result.
     *
     * @throws Exception
     */
    public function dumpDispatcher(string $path): void
    {
        $dump    = $this->buildDispatcherDump();
        $php     = "<?php\nreturn " . var_export($dump, true) . ";\n";
        $tmpPath = $path . ".tmp." . (string) getmypid();

        if (file_put_contents($tmpPath, $php) === false) {
            throw new Exception("Failed to write router cache temp file: " . $tmpPath);
        }

        if (!rename($tmpPath, $path)) {
            unlink($tmpPath);
            throw new Exception("Failed to commit router cache: " . $path);
        }
    }

    /**
     * Returns the processed action name
     */
    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * Returns the processed controller name
     */
    public function getControllerName(): string
    {
        return $this->controller;
    }

    /**
     * Returns an array of default parameters
     *
     * @return array<string, array|string>
     *
     * @phpstan-return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return [
            'namespace'  => $this->defaultNamespace,
            'module'     => $this->defaultModule,
            'controller' => $this->defaultController,
            'action'     => $this->defaultAction,
            'params'     => $this->defaultParams,
        ];
    }

    /**
     * @phpstan-return array<array-key, int|string>
     */
    public function getKeyRouteIds(): array
    {
        return $this->keyRouteIds;
    }

    /**
     * @phpstan-return array<string, int|string>
     */
    public function getKeyRouteNames(): array
    {
        return $this->keyRouteNames;
    }

    /**
     * Returns the route that matches the handled URI
     */
    public function getMatchedRoute(): RouteInterface | null
    {
        return $this->matchedRoute;
    }

    /**
     * Returns the sub expressions in the regular expression matched
     *
     * @phpstan-return mvc_router_matches
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Returns routes indexed by HTTP method, building the index if needed.
     * Unconstrained routes are stored under the "*" key.
     *
     * @phpstan-return mvc_router_method_buckets
     */
    public function getMethodRoutes(): array
    {
        if ($this->methodRoutesDirty) {
            $this->rebuildMethodIndex();
        }

        return $this->methodRoutes;
    }

    /**
     * Returns the processed module name
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Returns the processed namespace name
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /**
     * Returns the processed parameters
     *
     * @phpstan-return mvc_router_params
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get rewrite info. This info is read from $_GET["_url"].
     * This returns '/' if the rewrite information cannot be read
     */
    public function getRewriteUri(): string
    {
        /**
         * By default we use $_GET["url"] to obtain the rewrite information
         */
        if (empty($this->uriSource)) {
            /** @var string $url */
            $url = $_GET['_url'] ?? '';
            if (true !== empty($url)) {
                return $this->extractRealUri($url);
            }
        } else {
            /**
             * Otherwise use the standard $_SERVER["REQUEST_URI"]
             */
            /** @var string $url */
            $url = $_SERVER['REQUEST_URI'] ?? '';
            if (true !== empty($url)) {
                return $this->extractRealUri($url);
            }
        }

        return "/";
    }

    /**
     * Returns a route object by its id
     */
    public function getRouteById(mixed $routeId): bool | RouteInterface
    {
        /**
         * The route id is an integer or a string. The map uses it as a key.
         */
        /** @var array-key $routeKey */
        $routeKey = $routeId;

        if (isset($this->keyRouteIds[$routeKey])) {
            /**
             * The map is rebuilt with the routes list, so the index is
             * always present.
             */
            /** @var list<RouteInterface> $byId */
            $byId = $this->routes;
            /** @var int $indexById */
            $indexById = $this->keyRouteIds[$routeKey];

            return $byId[$indexById];
        }

        /**
         * @var int            $key
         * @var RouteInterface $route
         */
        foreach ($this->routes as $key => $route) {
            $id                     = $route->getRouteId();
            $this->keyRouteIds[$id] = $key;

            if ($id == $routeId) {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns a route object by its name
     */
    public function getRouteByName(string $name): bool | RouteInterface
    {
        if (isset($this->keyRouteNames[$name])) {
            /**
             * The map is rebuilt with the routes list, so the index is
             * always present.
             */
            /** @var non-empty-list<RouteInterface> $byName */
            $byName = $this->routes;
            /** @var int<0, max> $indexByName */
            $indexByName = $this->keyRouteNames[$name];

            return $byName[$indexByName];
        }

        foreach ($this->routes as $key => $route) {
            $routeName = $route->getName();

            if (!empty($routeName)) {
                $this->keyRouteNames[$routeName] = $key;

                if ($routeName === $name) {
                    return $route;
                }
            }
        }

        return false;
    }

    /**
     * Returns all the routes defined in the router
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Handles routing information received from the rewrite engine
     *
     *```php
     * // Passing a URL
     * $router->handle("/posts/edit/1");
     *```
     *
     * @throws Exception
     * @throws EventsException
     */
    public function handle(string $uri): void
    {
        if (empty($uri)) {
            /**
             * If 'uri' isn't passed as parameter it reads _GET["_url"]
             */
            $uri = $this->getRewriteUri();
        } else {
            $uri = $this->extractRealUri($uri);
        }

        /**
         * Remove extra slashes in the route
         */
        if ($this->removeExtraSlashes && $uri !== "/") {
            $handledUri = rtrim($uri, "/");
        } else {
            $handledUri = $uri;
        }

        if (empty($handledUri)) {
            $handledUri = "/";
        }

        $currentHostName    = null;
        $routeFound         = false;
        $parts              = [];
        $params             = [];
        $this->wasMatched   = false;
        $this->matchedRoute = null;

        $this->fireManagerEvent('router:beforeCheckRoutes');

        /**
         * Retrieve the request service from the container
         */
        $this->checkContainer(
            Exception::class,
            "the 'request' service"
        );

        /** @var RequestInterface $request */
        $request = $this->container->get("request");

        /**
         * Rebuild the method index if routes were added/changed since last handle
         */
        if ($this->methodRoutesDirty) {
            $this->rebuildMethodIndex();
        }

        /**
         * Build candidate list: routes matching the request method (pre-merged
         * with "*" routes at rebuild time). Routes are traversed in reversed
         * order (last registered wins).
         */
        $requestMethod   = $request->getMethod();
        $candidateRoutes = $this->candidatesByMethod[$requestMethod]
            ?? $this->candidatesByMethod["*"]
            ?? [];

        /**
         * Resolve the current hostname once if any hostname-constrained
         * route exists in the candidate bucket.
         */
        if (
            (isset($this->hostnameByMethod[$requestMethod]) && !empty($this->hostnameByMethod[$requestMethod]))
            || (isset($this->hostnameByMethod["*"]) && !empty($this->hostnameByMethod["*"]))
        ) {
            $currentHostName = $request->getHttpHost();
        }

        /**
         * Static-route fast path: O(1) lookup for literal URIs that are not
         * shadowed by a later-attached regex in the same bucket. Disabled
         * when an events manager is attached so per-route events keep firing
         * from the regular loop with their existing semantics.
         */
        if ($this->eventsManager === null) {
            $staticBucketMethod = null;

            if (
                isset($this->staticByMethod[$requestMethod][$handledUri])
                && !isset($this->staticShadowedByMethod[$requestMethod][$handledUri])
            ) {
                $staticBucketMethod = $requestMethod;
            } elseif (
                isset($this->staticByMethod["*"][$handledUri])
                && !isset($this->staticShadowedByMethod["*"][$handledUri])
            ) {
                $staticBucketMethod = "*";
            }

            if ($staticBucketMethod !== null) {
                $staticBucket = $this->staticByMethod[$staticBucketMethod][$handledUri];

                foreach (array_reverse($staticBucket) as $staticRoute) {
                    $staticHostname = $staticRoute->getHostName();

                    if (null !== $staticHostname) {
                        if (null === $currentHostName) {
                            $currentHostName = $request->getHttpHost();
                        }

                        if (!$currentHostName) {
                            continue;
                        }

                        $staticHostRegex = $staticRoute->getCompiledHostName();

                        if ($staticHostRegex !== null) {
                            $staticMatched = preg_match($staticHostRegex, $currentHostName);
                        } else {
                            $staticMatched = $currentHostName == $staticHostname;
                        }

                        if (!$staticMatched) {
                            continue;
                        }
                    }

                    $staticBeforeMatch = $staticRoute->getBeforeMatch();

                    if ($staticBeforeMatch !== null) {
                        if (!is_callable($staticBeforeMatch)) {
                            throw new BeforeMatchNotCallable();
                        }

                        $routeFound = $staticBeforeMatch($handledUri, $staticRoute, $this);

                        if (!$routeFound) {
                            continue;
                        }
                    }

                    $routeFound         = true;
                    $matches            = null;
                    $parts              = $staticRoute->getPaths();
                    $this->matchedRoute = $staticRoute;

                    break;
                }
            }
        }

        /**
         * Combined-regex fast path: one preg_match per chunk replaces N
         * per-route preg_matches. Disabled when events are attached or the
         * bucket has hostname constraints.
         */
        if (
            !$routeFound
            && $this->eventsManager === null
            && !isset($this->combinedRegexDisabled[$requestMethod])
            && isset($this->combinedRegexByMethod[$requestMethod])
        ) {
            $combinedChunks   = $this->combinedRegexByMethod[$requestMethod];
            $combinedMarkMaps = $this->combinedRegexMarkMap[$requestMethod];

            foreach ($combinedChunks as $combinedChunkIdx => $combinedChunk) {
                $combinedMatchesLocal = [];

                if (!preg_match($combinedChunk, $handledUri, $combinedMatchesLocal)) {
                    continue;
                }

                $combinedMarkLabel = $combinedMatchesLocal["MARK"];

                if (!isset($combinedMarkMaps[$combinedChunkIdx][$combinedMarkLabel])) {
                    continue;
                }

                $combinedRouteIdx  = $combinedMarkMaps[$combinedChunkIdx][$combinedMarkLabel];
                $combinedRoute     = $candidateRoutes[$combinedRouteIdx];
                $combinedRouteMeta = $this->routeMeta[$combinedRoute->getRouteId()];

                $combinedBeforeMatch = $combinedRouteMeta["beforeMatch"];

                if ($combinedBeforeMatch !== null) {
                    if (!is_callable($combinedBeforeMatch)) {
                        throw new BeforeMatchNotCallable();
                    }

                    if (!$combinedBeforeMatch($handledUri, $combinedRoute, $this)) {
                        continue;
                    }
                }

                $combinedPaths      = $combinedRoute->getPaths();
                $parts              = $combinedPaths;
                $matches            = $combinedMatchesLocal;
                $combinedConverters = $combinedRoute->getConverters();
                $this->matches      = $combinedMatchesLocal;
                $this->matchedRoute = $combinedRoute;
                $routeFound         = true;

                foreach ($combinedPaths as $combinedPart => $combinedPosition) {
                    if (!is_string($combinedPart)) {
                        throw new WrongPathsKey($combinedPart);
                    }

                    if (!is_string($combinedPosition) && !is_int($combinedPosition)) {
                        continue;
                    }

                    if (isset($combinedMatchesLocal[$combinedPosition])) {
                        $combinedMatchPosition = $combinedMatchesLocal[$combinedPosition];

                        if (is_array($combinedConverters) && isset($combinedConverters[$combinedPart])) {
                            $combinedConverter      = $combinedConverters[$combinedPart];
                            $parts[$combinedPart]   = $combinedConverter($combinedMatchPosition);
                            continue;
                        }

                        $parts[$combinedPart] = $combinedMatchPosition;
                    } else {
                        if (is_array($combinedConverters) && isset($combinedConverters[$combinedPart])) {
                            $combinedConverter    = $combinedConverters[$combinedPart];
                            $parts[$combinedPart] = $combinedConverter($combinedPosition);
                        } elseif (is_int($combinedPosition)) {
                            unset($parts[$combinedPart]);
                        }
                    }
                }

                break;
            }
        }

        if (!$routeFound) {
            foreach (array_reverse($candidateRoutes, true) as $routeIdx => $route) {
                $routeMeta = $this->routeMeta[$route->getRouteId()];
                $params    = [];
                $matches   = null;

            /**
             * Look for hostname constraints
             */
                $hostname = $routeMeta["hostname"];
                if (null !== $hostname) {
                    /**
                     * Check if the current hostname is the same as the route
                     */
                    if (null === $currentHostName) {
                        $currentHostName = $request->getHttpHost();
                    }

                    /**
                     * No HTTP_HOST, maybe in CLI mode?
                     */
                    if (!$currentHostName) {
                        continue;
                    }

                    /**
                     * Check if the hostname restriction is the same as the current
                     * in the route
                     */
                    $regexHostName = $routeMeta["hostRegex"];

                    if ($regexHostName !== null) {
                        $matched = preg_match($regexHostName, $currentHostName);
                    } else {
                        $matched = $currentHostName == $hostname;
                    }

                    if (!$matched) {
                        continue;
                    }
                }

                $this->fireManagerEvent('router:beforeCheckRoute', $route);

            /**
             * If the route has parentheses use preg_match
             */
                $pattern = $routeMeta["pattern"];

                if ($routeMeta["isRegex"]) {
                    $routeFound = preg_match($pattern, $handledUri, $matches);
                } else {
                    $routeFound = $pattern === $handledUri;
                }

            /**
             * Check for beforeMatch conditions
             */
                if ($routeFound) {
                    $this->fireManagerEvent('router:matchedRoute', $route);

                    $beforeMatch = $routeMeta["beforeMatch"];
                    if ($beforeMatch !== null) {
                        /**
                         * Check first if the callback is callable
                         */
                        if (!is_callable($beforeMatch)) {
                            throw new BeforeMatchNotCallable();
                        }

                        $routeFound = $beforeMatch($handledUri, $route, $this);
                    }
                } else {
                    $this->fireManagerEvent('router:notMatchedRoute', $route);
                }

                if ($routeFound) {
                    /**
                     * Start from the default paths
                     */
                    $paths = $route->getPaths();
                    $parts = $paths;

                    /**
                     * Check if the matches has variables
                     */
                    if (is_array($matches)) {
                        /**
                         * Get the route converters if any
                         */
                        $converters = $route->getConverters();

                        foreach ($paths as $part => $position) {
                            if (!is_string($part)) {
                                throw new WrongPathsKey($part);
                            }

                            if (!is_string($position) && !is_int($position)) {
                                continue;
                            }

                            if (isset($matches[$position])) {
                                $matchPosition = $matches[$position];
                                /**
                                 * Check if the part has a converter
                                 */
                                if (is_array($converters) && isset($converters[$part])) {
                                    $converter    = $converters[$part];
                                    $parts[$part] = $converter($matchPosition);

                                    continue;
                                }

                                /**
                                 * Update the parts if there is no converter
                                 */
                                $parts[$part] = $matchPosition;
                            } else {
                                /**
                                 * Apply the converters anyway
                                 */
                                if (is_array($converters) && isset($converters[$part])) {
                                    $converter    = $converters[$part];
                                    $parts[$part] = $converter($position);
                                } elseif (is_int($position)) {
                                    /**
                                     * Remove the path if the parameter was not
                                     * matched
                                     */
                                    unset($parts[$part]);
                                }
                            }
                        }

                        /**
                         * Update the matches generated by preg_match
                         */
                        $this->matches = $matches;
                    }

                    $this->matchedRoute = $route;

                    break;
                }
            }
        }

        /**
         * Update the wasMatched property indicating if the route was matched
         */
        $this->wasMatched = (bool)$routeFound;

        /**
         * The route wasn't found, try to use the not-found paths
         */
        if (!$routeFound) {
            $notFoundPaths = $this->notFoundPaths;

            if ($notFoundPaths !== null) {
                $parts      = Route::getRoutePaths($notFoundPaths);
                $routeFound = true;
            }
        }

        /**
         * Use default values before we overwrite them if the route is matched
         */
        $this->namespaceName = $this->defaultNamespace;
        $this->module        = $this->defaultModule;
        $this->controller    = $this->defaultController;
        $this->action        = $this->defaultAction;
        $this->params        = $this->defaultParams;

        if ($routeFound) {
            /**
             * Check for a namespace
             */
            if (isset($parts['namespace'])) {
                /**
                 * The matched value replaces the placeholder position, so
                 * these four parts hold strings here.
                 */
                /** @var string $namespacePart */
                $namespacePart       = $parts['namespace'];
                $this->namespaceName = $namespacePart;
                unset($parts['namespace']);
            }

            /**
             * Check for a module
             */
            if (isset($parts['module'])) {
                /** @var string $modulePart */
                $modulePart   = $parts['module'];
                $this->module = $modulePart;
                unset($parts['module']);
            }

            /**
             * Check for a controller
             */
            if (isset($parts['controller'])) {
                /** @var string $controllerPart */
                $controllerPart   = $parts['controller'];
                $this->controller = $controllerPart;
                unset($parts['controller']);
            }

            /**
             * Check for an action
             */
            if (isset($parts['action'])) {
                /** @var string $actionPart */
                $actionPart   = $parts['action'];
                $this->action = $actionPart;
                unset($parts['action']);
            }

            /**
             * Check for parameters
             */
            if (isset($parts["params"])) {
                $paramsStr = $parts["params"];
                if (is_string($paramsStr)) {
                    $strParams = trim($paramsStr, "/");

                    if ('' !== $strParams) {
                        $params = explode("/", $strParams);
                    }
                }

                unset($parts["params"]);
            }

            if (!empty($params)) {
                $this->params = array_merge($params, $parts);
            } else {
                $this->params = $parts;
            }
        }

        $this->fireManagerEvent('router:afterCheckRoutes');

        if ($this->pendingCache !== null) {
            $this->pendingCache->set($this->pendingCacheKey, $this->buildDispatcherDump());
            $this->pendingCache    = null;
            $this->pendingCacheKey = "";
        }
    }

    /**
     * Returns whether controller name should not be mangled
     */
    public function isExactControllerName(): bool
    {
        return true;
    }

    /**
     * File-shaped helper around loadDispatcherFromArray(). Includes the
     * file (opcache-friendly) and forwards the return value.
     *
     * @throws Exception
     */
    public function loadDispatcher(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception("Router cache not found: " . $path);
        }

        $dump = require $path;

        if (!is_array($dump)) {
            throw new Exception(
                "Router cache is corrupt or invalid (expected array, got "
                . gettype($dump) . "): " . $path
            );
        }

        /** @var array<string, mixed> $dump */

        $this->loadDispatcherFromArray($dump);
    }

    /**
     * Inverse of buildDispatcherDump(). Reconstructs every Route from the
     * scalar `routes` entries (preserving subclass and routeId), restores
     * every index, and marks the indexes clean so handle() skips rebuild.
     *
     * @throws Exception
     *
     * @phpstan-param array<string, mixed> $dump
     */
    public function loadDispatcherFromArray(array $dump): void
    {
        if (!isset($dump["version"])) {
            throw new Exception("Router cache is missing 'version' field");
        }

        /** @var scalar $dumpedVersion */
        $dumpedVersion = $dump["version"];
        $dumpVersion   = (int) $dumpedVersion;

        if ($dumpVersion !== 1) {
            throw new Exception(
                "Router cache version " . $dumpVersion
                . " is not supported (this build supports version 1)"
            );
        }

        if (!isset($dump["routes"])) {
            throw new Exception("Router cache is missing 'routes' field");
        }

        /**
         * The two guards above prove the cache holds a dump this build
         * writes.
         */
        /** @var mvc_router_dump $dump */

        $rebuiltRoutes = [];

        foreach ($dump["routes"] as $routeData) {
            $routeClass = $routeData["class"];
            $route      = new $routeClass(
                $routeData["pattern"],
                $routeData["paths"],
                $routeData["methods"]
            );

            if ($routeData["hostname"] !== null) {
                $route->setHostname($routeData["hostname"]);
            }

            if ($routeData["name"] !== null) {
                $route->setName($routeData["name"]);
            }

            $route->setRouteId($routeData["id"]);

            $beforeMatch = $routeData["beforeMatch"];
            if ($beforeMatch !== null) {
                $route->beforeMatch($beforeMatch);
            }

            $converters = $routeData["converters"];
            if (is_array($converters)) {
                foreach ($converters as $convName => $converter) {
                    $route->convert($convName, $converter);
                }
            }

            $rebuiltRoutes[] = $route;
        }

        $this->routes = $rebuiltRoutes;

        $methodRoutesRehydrated = [];
        foreach ($dump["methodRoutes"] as $innerKey => $innerVal) {
            $mostInnerArr = [];
            foreach ($innerVal as $scalarIdx) {
                $mostInnerArr[] = $this->routes[$scalarIdx];
            }
            $methodRoutesRehydrated[$innerKey] = $mostInnerArr;
        }

        $candidatesRehydrated = [];
        foreach ($dump["candidatesByMethod"] as $innerKey => $innerVal) {
            $mostInnerArr = [];
            foreach ($innerVal as $scalarIdx) {
                $mostInnerArr[] = $this->routes[$scalarIdx];
            }
            $candidatesRehydrated[$innerKey] = $mostInnerArr;
        }

        $staticRehydrated = [];
        foreach ($dump["staticByMethod"] as $innerKey => $innerVal) {
            $staticRehydrated[$innerKey] = [];
            foreach ($innerVal as $scalarSubKey => $mostInnerVal) {
                $mostInnerArr = [];
                foreach ($mostInnerVal as $scalarIdx) {
                    $mostInnerArr[] = $this->routes[$scalarIdx];
                }
                $staticRehydrated[$innerKey][$scalarSubKey] = $mostInnerArr;
            }
        }

        $this->methodRoutes           = $methodRoutesRehydrated;
        $this->candidatesByMethod     = $candidatesRehydrated;
        $this->staticByMethod         = $staticRehydrated;
        $this->staticShadowedByMethod = $dump["staticShadowedByMethod"];
        $this->hostnameByMethod       = $dump["hostnameByMethod"];
        $this->hostnameLessByMethod   = $dump["hostnameLessByMethod"];
        $this->combinedRegexByMethod  = $dump["combinedRegexByMethod"];
        $this->combinedRegexDisabled  = $dump["combinedRegexDisabled"];
        $this->combinedRegexMarkMap   = $dump["combinedRegexMarkMap"];
        $this->routeMeta              = $dump["routeMeta"];
        $this->keyRouteIds            = [];
        $this->keyRouteNames          = [];
        $this->methodRoutesDirty      = false;
    }

    /**
     * Loads routes from an array or Phalcon\Config\Config instance.
     *
     * ```php
     * $router->loadFromConfig(
     *     [
     *         'routes' => [
     *             [
     *                 'method'  => 'get',
     *                 'pattern' => '/users',
     *                 'paths'   => 'Users::index',
     *             ],
     *         ],
     *     ]
     * );
     * ```
     *
     * @return RouterInterface
     * @throws Exception
     *
     * @phpstan-return static
     */
    public function loadFromConfig(mixed $config): static
    {
        if (is_object($config)) {
            if (!($config instanceof ConfigInterface)) {
                throw new InvalidConfigSource();
            }

            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw new InvalidConfigSource();
        }

        /** @var array<string, mixed> $configData */
        $configData = $config;

        if (isset($configData['removeExtraSlashes'])) {
            $this->removeExtraSlashes((bool) $configData['removeExtraSlashes']);
        }

        if (isset($configData['defaults'])) {
            if (!is_array($configData['defaults'])) {
                throw new ConfigKeyMustBeArray("defaults");
            }
            /** @var mvc_router_defaults $configDefaults */
            $configDefaults = $configData['defaults'];
            $this->setDefaults($configDefaults);
        }

        if (isset($configData['routes'])) {
            if (!is_array($configData['routes'])) {
                throw new ConfigKeyMustBeArray("routes");
            }
            /** @var iterable<array<string, mixed>> $configRoutes */
            $configRoutes = $configData['routes'];
            foreach ($configRoutes as $routeData) {
                $this->addRouteFromConfig($routeData);
            }
        }

        if (isset($configData['groups'])) {
            if (!is_array($configData['groups'])) {
                throw new ConfigKeyMustBeArray("groups");
            }
            /** @var iterable<array<string, mixed>> $configGroups */
            $configGroups = $configData['groups'];
            foreach ($configGroups as $groupData) {
                $this->mountGroupFromConfig($groupData);
            }
        }

        if (isset($configData['notFound'])) {
            /** @var mvc_router_paths|string $notFoundConfig */
            $notFoundConfig = $configData['notFound'];
            $this->notFound($notFoundConfig);
        }

        return $this;
    }

    /**
     * Mounts a group of routes in the router
     *
     * @return RouterInterface
     * @throws EventsException
     * @throws Exception
     *
     * @phpstan-return static
     */
    public function mount(GroupInterface $group): static
    {
        $this->fireManagerEvent('router:beforeMount', $group);

        $groupRoutes = $group->getRoutes();

        if (empty($groupRoutes)) {
            throw new EmptyGroupOfRoutes();
        }

        /**
         * Get the before-match condition
         */
        $beforeMatch = $group->getBeforeMatch();

        if ($beforeMatch !== null) {
            foreach ($groupRoutes as $groupRoute) {
                $groupRoute->beforeMatch($beforeMatch);
            }
        }

        // Get the hostname restriction
        $hostname = $group->getHostName();

        if (null !== $hostname) {
            foreach ($groupRoutes as $groupRoute) {
                $groupRoute->setHostName($hostname);
            }
        }

        foreach ($groupRoutes as $groupRoute) {
            $this->attach($groupRoute);
        }

        return $this;
    }

    /**
     * Set a group of paths to be returned when none of the defined routes are
     * matched
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function notFound(mixed $paths): static
    {
        /**
         * The paths are an array of route paths, a string or null.
         */
        /** @var mvc_router_paths|string|null $notFoundPaths */
        $notFoundPaths = $paths;

        $this->notFoundPaths = $notFoundPaths;

        return $this;
    }

    /**
     * Set whether router must remove the extra slashes in the handled routes
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function removeExtraSlashes(bool $remove): static
    {
        $this->removeExtraSlashes = $remove;

        return $this;
    }

    /**
     * Sets the default action name
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function setDefaultAction(string $actionName): static
    {
        $this->defaultAction = $actionName;

        return $this;
    }

    /**
     * Sets the default controller name
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function setDefaultController(string $controllerName): static
    {
        $this->defaultController = $controllerName;

        return $this;
    }

    /**
     * Sets the name of the default module
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function setDefaultModule(string $moduleName): static
    {
        $this->defaultModule = $moduleName;

        return $this;
    }

    /**
     * Sets the name of the default namespace
     *
     * @return RouterInterface
     *
     * @phpstan-return static
     */
    public function setDefaultNamespace(string $namespaceName): static
    {
        $this->defaultNamespace = $namespaceName;

        return $this;
    }

    /**
     * Sets an array of default paths. If a route is missing a path the router
     * will use the defined here. This method must not be used to set a 404
     * route
     *
     *```php
     * $router->setDefaults(
     *     [
     *         "module" => "common",
     *         "action" => "index",
     *     ]
     * );
     *```
     *
     * @return RouterInterface
     *
     * @phpstan-param mvc_router_defaults $defaults
     *
     * @phpstan-return static
     */
    public function setDefaults(array $defaults): static
    {
        // Set a default namespace
        if (isset($defaults['namespace'])) {
            $this->defaultNamespace = (string)$defaults['namespace'];
        }

        // Set a default module
        if (isset($defaults['module'])) {
            $this->defaultModule = (string)$defaults['module'];
        }

        // Set a default controller
        if (isset($defaults['controller'])) {
            $this->defaultController = (string)$defaults['controller'];
        }

        // Set a default action
        if (isset($defaults['action'])) {
            $this->defaultAction = (string)$defaults['action'];
        }

        // Set default parameters
        if (isset($defaults['params'])) {
            /** @var mvc_router_params $defaultParams */
            $defaultParams       = $defaults['params'];
            $this->defaultParams = $defaultParams;
        }

        return $this;
    }

    /**
     * @return Router
     *
     * @phpstan-param array<array-key, int|string> $routeIds
     *
     * @phpstan-return static
     */
    public function setKeyRouteIds(array $routeIds): static
    {
        $this->keyRouteIds = $routeIds;

        return $this;
    }

    /**
     * @return Router
     *
     * @phpstan-param array<string, int|string> $routeNames
     *
     * @phpstan-return static
     */
    public function setKeyRouteNames(array $routeNames): static
    {
        $this->keyRouteNames = $routeNames;

        return $this;
    }

    /**
     * Sets the URI source. One of the URI_SOURCE_* constants
     *
     * ```php
     * $router->setUriSource(
     *     Router::URI_SOURCE_SERVER_REQUEST_URI
     * );
     * ```
     */
    public function setUriSource(int $uriSource): static
    {
        $this->uriSource = $uriSource;

        return $this;
    }

    /**
     * Cache-instance convenience wrapper. On cache hit, restores the
     * dispatcher immediately. On miss, defers cache population until the
     * next handle() completes - at which point buildDispatcherDump() is
     * written to the cache key.
     *
     * @throws Exception
     */
    public function useCache(
        CacheAdapterInterface $cache,
        string $key = "phalcon.router.dispatcher"
    ): void {
        if ($cache->has($key)) {
            $stored = $cache->get($key);

            if (!is_array($stored)) {
                throw new Exception(
                    "Router cache value at key '" . $key . "' is not an array"
                );
            }

            /** @var array<string, mixed> $stored */

            $this->loadDispatcherFromArray($stored);

            return;
        }

        $this->pendingCache    = $cache;
        $this->pendingCacheKey = $key;
    }

    /**
     * Checks if the router matches any of the defined routes
     */
    public function wasMatched(): bool
    {
        return $this->wasMatched;
    }

    /**
     * Adds a single route from a config array entry. Used by loadFromConfig.
     *
     * @throws Exception
     *
     * @phpstan-param array<string, mixed> $routeData
     */
    protected function addRouteFromConfig(array $routeData): void
    {
        if (!isset($routeData['pattern'])) {
            throw new MissingRouteConfigKey("pattern");
        }

        if (!isset($routeData['paths'])) {
            throw new MissingRouteConfigKey("paths");
        }

        /**
         * The two guards above prove both keys are present.
         */
        /** @var mvc_router_config_route $routeData */

        $pattern = $routeData['pattern'];
        $paths   = $routeData['paths'];
        $method  = '';

        if (isset($routeData['method'])) {
            $method = strtolower((string) $routeData['method']);
        }

        switch ($method) {
            case '':
            case 'connect':
            case 'delete':
            case 'get':
            case 'head':
            case 'options':
            case 'patch':
            case 'post':
            case 'purge':
            case 'put':
            case 'trace':
                $methodCall = 'add' . ucfirst($method);
                $route      = $this->{$methodCall}($pattern, $paths);
                break;
            default:
                throw new UnknownHttpMethod($method);
        }

        if (isset($routeData['name'])) {
            $route->setName((string) $routeData['name']);
        }
        if (isset($routeData['hostname'])) {
            $route->setHostname((string) $routeData['hostname']);
        }
    }

    protected function extractRealUri(string $uri): string
    {
        $urlParts = explode("?", $uri, 2);

        return $urlParts[0];
    }

    /**
     * Builds a Group from a config entry and mounts it. Used by loadFromConfig.
     *
     * @throws EventsException
     * @throws Exception
     *
     * @phpstan-param array<string, mixed> $groupData
     */
    protected function mountGroupFromConfig(array $groupData): void
    {
        /** @var mvc_router_paths|string|null $paths */
        $paths = $groupData['paths'] ?? null;
        $group = new Group($paths);

        if (isset($groupData['prefix'])) {
            /** @var scalar $groupPrefix */
            $groupPrefix = $groupData['prefix'];
            $group->setPrefix((string) $groupPrefix);
        }

        if (isset($groupData['hostname'])) {
            /** @var scalar $groupHostname */
            $groupHostname = $groupData['hostname'];
            $group->setHostname((string) $groupHostname);
        }

        $routes = $groupData['routes'] ?? [];

        if (!is_array($routes)) {
            throw new GroupRoutesMustBeArray();
        }

        /** @var iterable<array<string, mixed>> $routes */

        foreach ($routes as $routeData) {
            if (!isset($routeData['pattern'])) {
                throw new MissingGroupRouteKey("pattern");
            }
            if (!isset($routeData['paths'])) {
                throw new MissingGroupRouteKey("paths");
            }

            /**
             * The two guards above prove both keys are present.
             */
            /** @var mvc_router_config_route $routeData */

            $pattern    = $routeData['pattern'];
            $routePaths = $routeData['paths'];
            $method     = '';

            if (isset($routeData['method'])) {
                $method = strtolower((string) $routeData['method']);
            }

            switch ($method) {
                case '':
                case 'connect':
                case 'delete':
                case 'get':
                case 'head':
                case 'options':
                case 'patch':
                case 'post':
                case 'purge':
                case 'put':
                case 'trace':
                    $methodCall = 'add' . ucfirst($method);
                    $route      = $group->{$methodCall}($pattern, $routePaths);
                    break;
                default:
                    throw new UnknownHttpMethod($method);
            }

            if (isset($routeData['name'])) {
                $route->setName((string) $routeData['name']);
            }
        }

        $this->mount($group);
    }

    /**
     * Rebuilds the HTTP-method index from the current routes array.
     * Routes with no HTTP method constraint are filed under "*".
     */
    protected function rebuildMethodIndex(): void
    {
        $index = [];

        foreach ($this->routes as $route) {
            $methods = $route->getHttpMethods();

            if (null === $methods) {
                $index["*"][] = $route;
            } else {
                if (is_string($methods)) {
                    $methods = [$methods];
                }

                foreach ($methods as $method) {
                    $index[$method][] = $route;
                }
            }
        }

        $this->methodRoutes           = $index;
        $this->candidatesByMethod     = [];
        $this->routeMeta              = [];
        $this->staticByMethod         = [];
        $this->staticShadowedByMethod = [];

        $starRoutes = $this->methodRoutes["*"] ?? [];

        foreach ($this->methodRoutes as $method => $methodSpecific) {
            if ($method === "*") {
                $this->candidatesByMethod["*"] = $starRoutes;
                continue;
            }

            /**
             * Build the bucket by walking the routes in their original
             * attach order, keeping both method-specific routes and the
             * unconstrained "*" routes. array_merge() would place every
             * method-specific route ahead of the "*" routes regardless of
             * when each was attached, inverting the reverse-iteration
             * priority that route matching relies on (see #17062).
             */
            $this->candidatesByMethod[$method] = [];

            foreach ($this->routes as $route) {
                $methods = $route->getHttpMethods();

                if (null === $methods) {
                    $this->candidatesByMethod[$method][] = $route;
                } elseif (is_string($methods)) {
                    if ($methods === $method) {
                        $this->candidatesByMethod[$method][] = $route;
                    }
                } elseif (in_array($method, $methods, true)) {
                    $this->candidatesByMethod[$method][] = $route;
                }
            }
        }

        /**
         * Single-source per-route metadata cache: one entry per route,
         * keyed by intrinsic id.
         */
        foreach ($this->routes as $candidateRoute) {
            $candidatePattern = $candidateRoute->getCompiledPattern();

            $this->routeMeta[$candidateRoute->getRouteId()] = [
                "pattern"     => $candidatePattern,
                "isRegex"     => str_contains($candidatePattern, "^"),
                "hostname"    => $candidateRoute->getHostname(),
                "hostRegex"   => $candidateRoute->getCompiledHostName(),
                "beforeMatch" => $candidateRoute->getBeforeMatch(),
            ];
        }

        /**
         * Build the static-route hash + shadow flags.
         */
        foreach ($this->candidatesByMethod as $method => $candidates) {
            foreach ($candidates as $bucketRoute) {
                $bucketPattern = $bucketRoute->getCompiledPattern();

                if (!str_contains($bucketPattern, "^")) {
                    $this->staticByMethod[$method][$bucketPattern][] = $bucketRoute;

                    /**
                     * A later static route for a URI overrides an earlier regex
                     * that shadowed it, so clear any stale shadow flag - the
                     * last-registered route must win.
                     */
                    if (isset($this->staticShadowedByMethod[$method][$bucketPattern])) {
                        unset($this->staticShadowedByMethod[$method][$bucketPattern]);
                    }
                } elseif (isset($this->staticByMethod[$method])) {
                    foreach ($this->staticByMethod[$method] as $staticUri => $_unusedList) {
                        if (preg_match($bucketPattern, $staticUri)) {
                            $this->staticShadowedByMethod[$method][$staticUri] = true;
                        }
                    }
                }
            }
        }

        /**
         * Hostname bucketing: split each method bucket into hostname-keyed
         * sub-buckets and a hostname-less list.
         */
        $this->hostnameByMethod     = [];
        $this->hostnameLessByMethod = [];

        foreach ($this->candidatesByMethod as $method => $candidates) {
            $this->hostnameByMethod[$method]     = [];
            $this->hostnameLessByMethod[$method] = [];

            foreach ($candidates as $bucketIdx => $bucketRoute) {
                $bucketHostname = $bucketRoute->getHostname();

                if ($bucketHostname === null) {
                    $this->hostnameLessByMethod[$method][] = $bucketIdx;
                } else {
                    $this->hostnameByMethod[$method][$bucketHostname][] = $bucketIdx;
                }
            }
        }

        /**
         * Combined-regex builder: for each method bucket without hostname
         * constraints, combine all regex routes into a chunked PCRE pattern
         * list with (?|...) branch reset and (*:N) mark labels.
         */
        $this->combinedRegexByMethod = [];
        $this->combinedRegexMarkMap  = [];
        $this->combinedRegexDisabled = [];

        foreach ($this->candidatesByMethod as $method => $candidates) {
            if (!empty($this->hostnameByMethod[$method])) {
                $this->combinedRegexDisabled[$method] = true;
                continue;
            }

            $combinedAlternatives = [];
            $combinedMark         = [];

            foreach ($candidates as $bucketIdx => $bucketRoute) {
                $bucketPattern = $bucketRoute->getCompiledPattern();

                if (!str_contains($bucketPattern, '^')) {
                    continue;
                }

                $combinedBodyMatch = [];
                if (!preg_match('/^#\\^(.+)\\$#u$/', $bucketPattern, $combinedBodyMatch)) {
                    $this->combinedRegexDisabled[$method] = true;
                    $combinedAlternatives                 = [];
                    break;
                }

                $combinedBody                       = $combinedBodyMatch[1];
                $combinedAlternatives[]             = $combinedBody . '(*:' . $bucketIdx . ')';
                $combinedMark[(string) $bucketIdx]  = $bucketIdx;
            }

            if (isset($this->combinedRegexDisabled[$method])) {
                continue;
            }

            if (empty($combinedAlternatives)) {
                continue;
            }

            /**
             * Reverse so first-match-wins gives reverse-attach. Chunk into
             * groups of REGEX_CHUNK_SIZE. chunks[0] holds LATEST-attached.
             */
            $combinedAlternatives = array_reverse($combinedAlternatives);
            $reversedMarkIds      = array_reverse(array_keys($combinedMark));

            $chunkedPatterns = [];
            $chunkedMarkMaps = [];
            $chunkOffset     = 0;

            while ($chunkOffset < count($combinedAlternatives)) {
                $chunkSlice      = array_slice($combinedAlternatives, $chunkOffset, self::REGEX_CHUNK_SIZE);
                $chunkMarkSubset = array_slice($reversedMarkIds, $chunkOffset, self::REGEX_CHUNK_SIZE);
                $chunkSliceMap   = [];

                foreach ($chunkMarkSubset as $chunkMarkId) {
                    $chunkSliceMap[$chunkMarkId] = $combinedMark[$chunkMarkId];
                }

                $chunkedPatterns[] = '#^(?|' . implode('|', $chunkSlice) . ')$#u';
                $chunkedMarkMaps[] = $chunkSliceMap;
                $chunkOffset      += self::REGEX_CHUNK_SIZE;
            }

            $this->combinedRegexByMethod[$method] = $chunkedPatterns;
            $this->combinedRegexMarkMap[$method]  = $chunkedMarkMaps;
        }

        $this->methodRoutesDirty = false;
    }
}
