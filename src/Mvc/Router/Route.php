<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\Router;
use Phalcon\Support\Str\Uncamelize;

use IntlChar;
/**
 * Phalcon\Mvc\Router\Route
 *
 * This class represents every route added to the router
 */
class Route implements RouteInterface
{
    protected $beforeMatch;
    protected string $compiledPattern;
    protected $converters;
    protected $group;
    protected $hostname;
    protected int $id; 
    protected $httpMethods;
    protected $match;
    protected $name;
    protected $paths;
    protected string $pattern;

    // $id is passed to constructor, the caller must manage and use a generator object, or some other method.

    /**
     * Phalcon\Mvc\Router\Route constructor
     */
    public function __construct(int $id, string $pattern, 
             $paths = null,  $httpMethods = null)
    {
        $this->id = $id;
        $this->pattern = $pattern;
        $this->paths = $paths;
        $this->httpMethods = $httpMethods;
        
        // Configure the route (extract parameters, paths, etc)
        $this->reConfigure($this->pattern, $this->paths);

        // Update the HTTP method constraints
        $this->via($this->httpMethods);


       
    }

    /**
     * Sets a callback that is called if the route is matched.
     * The developer can implement any arbitrary conditions here
     * If the callback returns false the route is treated as not matched
     *
     *```php
     * $router->add(
     *     "/login",
     *     [
     *         "module"     => "admin",
     *         "controller" => "session",
     *     ]
     * )->beforeMatch(
     *     function ($uri, $route) {
     *         // Check if the request was made with Ajax
     *         if ($_SERVER["HTTP_X_REQUESTED_WITH"] === "xmlhttprequest") {
     *             return false;
     *         }
     *
     *         return true;
     *     }
     * );
     *```
     */
    public function beforeMatch($callback): RouteInterface
    {
        $this->beforeMatch = $callback;

        return $this;
    }
    
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Replaces placeholders from pattern returning a valid PCRE regular expression
     */
    public function compilePattern(string $pattern): string
    {
        // If a pattern contains ':', maybe there are placeholders to replace
        if (strpos($pattern, ":")!==false) {
            // This is a pattern for valid identifiers
            $idPattern = "/([\\w0-9\\_\\-]+)";

            // Replace the module part
            if (strpos($pattern, "/:module")!==false) {
                $pattern = str_replace("/:module", $idPattern, $pattern);
            }

            // Replace the controller placeholder
            if (strpos($pattern,  "/:controller")!==false) {
                $pattern = str_replace("/:controller", $idPattern, $pattern);
            }

            // Replace the namespace placeholder
            if (strpos($pattern,  "/:namespace")!==false) {
                $pattern = str_replace("/:namespace", $idPattern, $pattern);
            }

            // Replace the action placeholder
            if (strpos($pattern, "/:action")!==false) {
                $pattern = str_replace("/:action", $idPattern, $pattern);
            }

            // Replace the params placeholder
            if (strpos($pattern, "/:params")!==false) {
                $pattern = str_replace("/:params", "(/.*)*", $pattern);
            }

            // Replace the int placeholder
            if (strpos($pattern, "/:int")!==false) {
                $pattern = str_replace("/:int", "/([0-9]+)", $pattern);
            }
        }

        /**
         * Check if the pattern has parentheses or square brackets in order to
         * add the regex delimiters
         */
        if ((strpos($pattern, "(")!==false) ||(strpos($pattern, "[")!==false)) {
            return "#^" . $pattern . "$#";
        }

        return $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(string $name, $converter): RouteInterface
    {
        $this->converters[name] = converter;

        return this;
    }

    /**
     * Extracts parameters from a string
     * TODO: return  array | bool
     * There is no usage of  { $this } in this function, it should be static
     * Given the intensive character processing, and unicode potential,
     * it needs to be coded in C or C++, unless using zephir compiled phalcon.
     * Not quite happy
     */
    public function extractNamedParams(string $pattern)
    {
        $prevChp = 0;
        $bracketCount = 0;  $parenthesesCount = 0;  $foundPattern = 0;
        $intermediate = 0;  $numberMatches = 0;
        
        if (strlen($pattern) === 0) {
            return false;
        }

        $cp_underscore = IntlChar::ord('_');
        $cp_dash = IntlChar::ord('-');
        $cp_colon = IntlChar::ord(':');
        $cp_lbt = IntlChar::ord('{');
        $cp_rbt = IntlChar::ord('}');
        $cp_lps = IntlChar::ord('(');
        $cp_rps = IntlChar::ord(')');
        $cp_bsl = IntlChar::ord('\\');
        
        $matches = [];
        $route = "";
        $notValid = false;
        $chars = preg_split('//u', $pattern, null, PREG_SPLIT_NO_EMPTY);
         foreach($chars as $cindex => $ch){
            $ch_pt = IntlChar::ord($ch);
            if ($parenthesesCount === 0) {
                if ($ch_pt === $cp_lbt) {
                    if ($bracketCount === 0)  {
                        $marker = $cindex + 1;
                        $intermediate = 0;
                        $notValid = false;
                    }
                    $bracketCount++;
                } elseif ($ch_pt === $cp_rbt) {
                    $bracketCount--;
                    if ($intermediate > 0) {
                        if ($bracketCount === 0) {
                            $numberMatches++;
                            $variable = null;
                            $regexp = null;
                            // the substring is an  slice of $chars array
                            $item = array_slice ($chars, $marker, $cindex  - $marker);
                            $item_str = implode('',$item); // need this later
                            foreach($item as $cursorVar => $chv) {
                                $cpt = IntlChar::ord($chv);
                                if ( $cpt === 0)  { // how did \0 get here?
                                    break;
                                }
                                if (($cursorVar === 0) && !IntlChar::isalpha($cpt)) {
                                    $notValid = true;
                                    break;
                                }
                                if (IntlChar::isalnum($cpt) || 
                                        $cpt === $cp_dash || $cpt === $cp_underscore || $cpt ===  $cp_colon) {
                                    if ($cpt ===  $cp_colon) {
                                            $variable = implode('', array_slice($item, 0, $cursorVar));
                                            $regexp =   array_slice($item, $cursorVar + 1);
                                            break;
                                    }
                                } else {
                                    $notValid = true;
                                    break;
                                }
                            }

                            if ( !$notValid) {
                                $tmp = $numberMatches;
                                if (!empty($variable) && !empty($regexp)){
                                    $foundPattern = 0;
                                    foreach($regexp as $ch){
                                        $cpt = IntlChar::ord($ch);
                                        if ($cpt === 0) {
                                            break;
                                        }
                                        if (!$foundPattern) {
                                            if ($cpt === $cp_lps) {
                                                $foundPattern = 1;
                                            }
                                        } else {
                                            if ($cpt === $cp_rps)  {
                                                $foundPattern = 2;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    $rxstr = implode('',$regexp);
                                    if ($foundPattern != 2) {
                                        $route .= "(" . $rxstr . ")";
                                    } else {
                                        $route .= $rxstr;
                                    }
                                    $matches[$variable] = $tmp;
                                } else {
                                    $route .= "([^/]*)";
                                    $matches[$item_str] = $tmp;
                                }
                            } else {
                                $route .= "{" . $item_str . "}";
                            }
                            continue;
                        }
                    }
                }
            }

            if ($bracketCount === 0) {
                if ($ch_pt === $cp_lps) {
                    $parenthesesCount++;
                } elseif ($ch_pt === $cp_rps) {
                    $parenthesesCount--;
                    if ($parenthesesCount === 0) {
                        $numberMatches++;
                    }
                }
            }

            if ($bracketCount > 0) {
                $intermediate++;
            } else {
                if (($parenthesesCount === 0) && ($prevChp !== $cp_bsl)) {
                    if (strpos(".+|#", $ch) !== false){
                        $route .= '\\';
                    }
                }
                $route .= $ch;
                $prevChp = $ch_pt;
            }
        }
        return [$route, $matches];
    }

    /**
     * Returns the 'before match' callback if any
     */
    public function getBeforeMatch() : callable
    {
        return $this->beforeMatch;
    }

    /**
     * Returns the route's compiled pattern
     */
    public function getCompiledPattern(): string
    {
        return $this->compiledPattern;
    }

    /**
     * Returns the router converter
     */
    public function getConverters() : array
    {
        return $this->converters;
    }

    /**
     * Returns the group associated with the route
     */
    public function getGroup():  ?GroupInterface
    {
        return $this->group;
    }

    /**
     * Returns the HTTP methods that constraint matching the route
     * TODO : return array | string
     */
    public function getHttpMethods()
    {
        return $this->httpMethods;
    }

    /**
     * Returns the hostname restriction if any
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * Returns the 'match' callback if any
     */
    public function getMatch() : ?callable
    {
        return $this->match;
    }

    /**
     * Returns the route's name
     */
    public function getName(): string
    {
        $name = $this->name;
        if ($name === null)
        {
            $name = (string) $this->id;
            $this->name = $name;
        }
        return $name;
    }

    /**
     * Returns the paths
     */
    public function getPaths() : array
    {
        return $this->paths;
    }

    /**
     * Returns the route's pattern
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the paths using positions as keys and names as values
     */
    public function getReversedPaths() : array
    {
        return array_flip(
            $this->paths
        );
    }

    /**
     * Returns the route's id
     */
    public function getRouteId(): string
    {
        return $this->id;
    }

    /**
     * Returns routePaths
     */
    public static function getRoutePaths($paths = null) : array
    {
        //varmoduleName, controllerName, actionName, parts, routePaths,
            //realClassName, namespaceName;

        if ($paths === null) {
            $paths = [];
        }

        if (is_string($paths)) {
            $moduleName = null;
                $controllerName = null;
                $actionName = null;

            // Explode the short paths using the :: separator
            $parts = explode("::", $paths);

            // Create the array paths dynamically

            switch (count($parts)) {
                case 3:
                    $moduleName = $parts[0];
                        $controllerName = parts[1];
                        $actionName = parts[2];
                    break;

                case 2:
                    $controllerName = parts[0];
                        $actionName = parts[1];
                    break;

                case 1:
                    $controllerName = parts[0];
                    break;
            }

            $routePaths = [];

            // Process module name
            if ($moduleName !== null) {
                $routePaths["module"] = $moduleName;
            }

            // Process controller name
            if ($controllerName !== null) {
                // Check if we need to obtain the namespace
                if (strpos($controllerName, "\\") !== false){
                    // Extract the real class name from the namespaced class
                    $realClassName = get_class_ns($controllerName);

                    // Extract the namespace from the namespaced class
                    $namespaceName = get_ns_class($controllerName);

                    // Update the namespace
                    if ($namespaceName) {
                        $routePaths["namespace"] = $namespaceName;
                    }
                } else {
                    $realClassName = $controllerName;
                }

                // Always pass the controller to lowercase
                $routePaths["controller"] = Uncamelize::fn(realClassName);
            }

            // Process action name
            if ($actionName !== null) {
                $routePaths["action"] = $actionName;
            }
        } else {
            $routePaths = $paths;
        }

        if (!is_array($routePaths)) {
            throw new Exception("The route contains invalid paths");
        }

        return $routePaths;
    }

    /**
     * Allows to set a callback to handle the request directly in the route
     *
     *```php
     * $router->add(
     *     "/help",
     *     []
     * )->match(
     *     function () {
     *         return $this->getResponse()->redirect("https://support.google.com/", true);
     *     }
     * );
     *```
     */
    public function match($callback): RouteInterface
    {
        $this->match = callback;

        return this;
    }

    /**
     * Reconfigure the route adding a new pattern and a set of paths
     */
    public function reConfigure(string $pattern, $paths = null) : void
    {
        //varroutePaths, pcrePattern, compiledPattern, extracted;

        $routePaths = self::getRoutePaths($paths);

        /**
         * If the route starts with '#' we assume that it is a regular expression
         */
        if (!str_starts_with($pattern, "#")) {
            if (strpos($pattern, "{")!==false) {
                /**
                 * The route has named parameters so we need to extract them
                 */
                $extracted = $this->extractNamedParams($pattern);
                 $pcrePattern =$extracted[0];
                 $routePaths = array_merge($routePaths, $extracted[1]);
            } else {
                $pcrePattern = $pattern;
            }

            /**
             * Transform the route's pattern to a regular expression
             */
            $compiledPattern = $this->compilePattern($pcrePattern);
        } else {
            $compiledPattern = $pattern;
        }

        /**
         * Update the original pattern
         */
        $this->pattern = $pattern;

        /**
         * Update the compiled pattern
         */
        $this->compiledPattern = $compiledPattern;

        /**
         * Update the route's paths
         */
        $this->paths = $routePaths;
    }

    /**
     * Resets the internal route id generator
     */
    public static function reset() : void
    {
        throw new Exception("Generator function for Route Id  is now external");
    }

    /**
     * Sets the group associated with the route
     */
    public function setGroup( GroupInterface  $group): RouteInterface
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Sets a set of HTTP methods that constraint the matching of the route (alias of via)
     *
     *```php
     * $route->setHttpMethods("GET");
     *
     * $route->setHttpMethods(
     *     [
     *         "GET",
     *         "POST",
     *     ]
     * );
     *```
     */
    public function setHttpMethods($httpMethods): RouteInterface
    {
        return $this->via(httpMethods);
    }

    /**
     * Sets a hostname restriction to the route
     *
     *```php
     * $route->setHostname("localhost");
     *```
     */
    public function setHostname(string $hostname): RouteInterface
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Sets the route's name
     *
     *```php
     * $router->add(
     *     "/about",
     *     [
     *         "controller" => "about",
     *     ]
     * )->setName("about");
     *```
     */
    public function setName(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set one or more HTTP methods that constraint the matching of the route
     *
     *```php
     * $route->via("GET");
     *
     * $route->via(
     *     [
     *         "GET",
     *         "POST",
     *     ]
     * );
     *```
     */
    public function via($httpMethods): RouteInterface
    {
        $this->httpMethods = $httpMethods;

        return $this;
    }
}
