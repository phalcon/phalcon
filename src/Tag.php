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

namespace Phalcon;

use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Link\Link;
use Phalcon\Html\Link\Serializer\Header;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Support\Helper\Str\Friendly;
use Phalcon\Tag\Exception;
use Phalcon\Tag\Select;

use function array_merge;
use function array_reverse;
use function htmlspecialchars;
use function implode;
use function is_array;
use function is_resource;
use function is_scalar;
use function is_string;
use function str_replace;

use const PHP_EOL;

/**
 * Phalcon\Tag is designed to simplify building of HTML tags.
 * It provides a set of helpers to generate HTML in a dynamic way.
 * This component is a class that you can extend to add more helpers.
 */
class Tag
{
    public const HTML32               = 1;
    public const HTML401_FRAMESET     = 4;
    public const HTML401_STRICT       = 2;
    public const HTML401_TRANSITIONAL = 3;
    public const HTML5                = 5;
    public const XHTML10_FRAMESET     = 8;
    public const XHTML10_STRICT       = 6;
    public const XHTML10_TRANSITIONAL = 7;
    public const XHTML11              = 9;
    public const XHTML20              = 10;
    public const XHTML5               = 11;

    /**
     * @var bool
     */
    protected static bool $autoEscape = true;

    /**
     * DI Container
     *
     * @var DiInterface|null
     */
    protected static DiInterface | null $container = null;

    /**
     * Pre-assigned values for components
     *
     * @var array
     */
    protected static array $displayValues;

    /**
     * @var array
     */
    protected static array $documentAppendTitle = [];

    /**
     * @var array
     */
    protected static array $documentPrependTitle = [];

    /**
     * HTML document title
     *
     * @var string|null
     */
    protected static string | null $documentTitle = "";

    /**
     * @var string|null
     */
    protected static string | null $documentTitleSeparator = "";

    /**
     * @var int
     */
    protected static int $documentType = 11;

    /**
     * @var EscaperInterface|null
     */
    protected static EscaperInterface | null $escaperService = null;

    /**
     * @var UrlInterface|null
     */
    protected static UrlInterface | null $urlService = null;

    /**
     * Appends a text to current document title
     *
     * @param array|string $title
     *
     * @return void
     */
    public static function appendTitle(array | string $title): void
    {
        if (is_array($title)) {
            self::$documentAppendTitle = $title;
        } else {
            self::$documentAppendTitle[] = $title;
        }
    }

    /**
     * Builds an HTML input[type="check"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     */
    public static function checkField(array | string $parameters): string
    {
        return self::inputFieldChecked("checkbox", $parameters);
    }

    /**
     * Builds an HTML input[type="color"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function colorField(array | string $parameters): string
    {
        return self::inputField("color", $parameters);
    }

    /**
     * Builds an HTML input[type="date"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function dateField(array | string $parameters): string
    {
        return self::inputField("date", $parameters);
    }

    /**
     * Builds an HTML input[type="datetime"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function dateTimeField(array | string $parameters): string
    {
        return self::inputField("datetime", $parameters);
    }

    /**
     * Builds an HTML input[type="datetime-local"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function dateTimeLocalField(array | string $parameters): string
    {
        return self::inputField("datetime-local", $parameters);
    }

    /**
     * Alias of Phalcon\Tag::setDefault()
     *
     * @param string $id
     * @param mixed  $value
     *
     * @return void
     */
    public static function displayTo(string $id, mixed $value): void
    {
        self::setDefault($id, $value);
    }

    /**
     * Builds an HTML input[type="email"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function emailField(array | string $parameters): string
    {
        return self::inputField("email", $parameters);
    }

    /**
     * Builds an HTML close FORM tag
     *
     * @return string
     */
    public static function endForm(): string
    {
        return "</form>";
    }

    /**
     * Builds an HTML input[type="file"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function fileField(array | string $parameters): string
    {
        return self::inputField("file", $parameters);
    }

    /**
     * Builds an HTML FORM tag
     *
     * @param array|string $parameters = [
     *                                 'method'     => 'post',
     *                                 'action'     => '',
     *                                 'parameters' => '',
     *                                 'name'       => '',
     *                                 'class'      => '',
     *                                 'id'         => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     * @throws Url\Exception
     */
    public static function formLegacy(array | string $parameters): string
    {
        $params = !is_array($parameters) ? [$parameters] : $parameters;

        $paramsAction = $params[0] ?? "";
        $paramsAction = $params["action"] ?? $paramsAction;

        /**
         * By default, the method is POST
         */
        $params["method"] = $params["method"] ?? "post";

        $action = null;

        if (!empty($paramsAction)) {
            $action = self::getUrlService()
                          ->get($paramsAction)
            ;
        }

        /**
         * Check for extra parameters
         */
        if (isset($params["parameters"])) {
            $action .= "?" . $params["parameters"];
        }

        if (!empty($action)) {
            $params["action"] = $action;
        }

        return self::renderAttributes("<form", $params) . ">";
    }

    /**
     * Converts texts into URL-friendly titles
     *
     * @param string       $text
     * @param string       $separator
     * @param bool         $lowercase
     * @param array|string $replace
     *
     * @return string
     * @throws Exception
     */
    public static function friendlyTitle(
        string $text,
        string $separator = "-",
        bool $lowercase = true,
        array | string $replace = []
    ): string {
        try {
            return (new Friendly())->__invoke($text, $separator, $lowercase, $replace);
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Internally gets the request dispatcher
     *
     * @return DiInterface
     */
    public static function getDI(): DiInterface
    {
        if (null === self::$container) {
            self::$container = Di::getDefault();
        }

        return self::$container;
    }

    /**
     * Get the document type declaration of content
     */
    public static function getDocType(): string
    {
        return match (self::$documentType) {
            1       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">"
                . PHP_EOL,
            2       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/html4/strict.dtd\">"
                . PHP_EOL,
            3       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/html4/loose.dtd\">"
                . PHP_EOL,
            4       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/html4/frameset.dtd\">"
                . PHP_EOL,
            6       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">"
                . PHP_EOL,
            7       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">"
                . PHP_EOL,
            8       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">"
                . PHP_EOL,
            9       => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">"
                . PHP_EOL,
            10      => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 2.0//EN\""
                . PHP_EOL
                . "\t\"http://www.w3.org/MarkUp/DTD/xhtml2.dtd\">"
                . PHP_EOL,
            5, 11   => "<!DOCTYPE html>" . PHP_EOL,
            default => "",
        };
    }

    /**
     * Obtains the 'escaper' service if required
     *
     * @param array $parameters
     *
     * @return EscaperInterface|null
     * @throws Exception
     */
    public static function getEscaper(array $parameters): EscaperInterface | null
    {
        $autoescape = isset($parameters["escape"])
            ? $parameters["escape"]
            : self::$autoEscape;

        if (true !== $autoescape) {
            return null;
        }

        return self::getEscaperService();
    }

    /**
     * Returns an Escaper service from the default DI
     *
     * @return EscaperInterface
     * @throws Exception
     */
    public static function getEscaperService(): EscaperInterface
    {
        if (null === self::$escaperService) {
            $container = self::getDI();

            self::$escaperService = $container->getShared("escaper");
        }

        return self::$escaperService;
    }

    /**
     * Gets the current document title. The title will be automatically escaped.
     *
     * @param bool $prepend
     * @param bool $append
     *
     * @return string
     * @throws Exception
     */
    public static function getTitle(
        bool $prepend = true,
        bool $append = true
    ): string {
        $escaper                = self::getEscaperService();
        $items                  = [];
        $output                 = "";
        $documentTitle          = $escaper->html(self::$documentTitle);
        $documentTitleSeparator = $escaper->html(self::$documentTitleSeparator);

        if (true === $prepend) {
            $documentPrependTitle = self::$documentPrependTitle;

            if (!empty($documentPrependTitle)) {
                $reverse = array_reverse($documentPrependTitle);
                foreach ($reverse as $title) {
                    $items[] = $escaper->html($title);
                }
            }
        }

        if (!empty($documentTitle)) {
            $items[] = $documentTitle;
        }

        if (true === $append) {
            $documentAppendTitle = self::$documentAppendTitle;

            if (!empty($documentAppendTitle)) {
                foreach ($documentAppendTitle as $title) {
                    $items[] = $escaper->html($title);
                }
            }
        }

        if (empty($documentTitleSeparator)) {
            $documentTitleSeparator = "";
        }

        if (!empty($items)) {
            $output = implode($documentTitleSeparator, $items);
        }

        return $output;
    }

    /**
     * Gets the current document title separator
     *
     * @return string
     */
    public static function getTitleSeparator(): string
    {
        return self::$documentTitleSeparator;
    }

    /**
     * Returns a URL service from the default DI
     *
     * @return Url
     * @throws Exception
     */
    public static function getUrlService(): Url
    {
        if (null === self::$urlService) {
            $container = self::getDI();

            self::$urlService = $container->getShared("url");
        }

        return self::$urlService;
    }

    /**
     * Every helper calls this function to check whether a component has a
     * predefined value using Phalcon\Tag::setDefault() or value from $_POST
     *
     * @param int|string $name
     * @param array      $parameters
     *
     * @return mixed|null
     */
    public static function getValue(int | string $name, array $parameters = [])
    {
        $value = $parameters["value"] ?? null;
        if (null === $value) {
            /**
             * Check if there is a predefined value for it
             */
            $value = self::$displayValues[$name] ?? null;
            if (null === $value) {
                /**
                 * Check if there is a post value for the item
                 */
                $value = $_POST[$name] ?? null;
            }
        }

        return $value;
    }

    /**
     * Check if a helper has a default value set using Phalcon\Tag::setDefault()
     * or value from $_POST
     *
     * @param int|string $name
     *
     * @return bool
     */
    public static function hasValue(int | string $name): bool
    {
        /**
         * Check if there is a predefined or a POST value for it
         */
        return isset(self::$displayValues[$name]) || isset($_POST[$name]);
    }

    /**
     * Builds a HTML input[type="hidden"] tag
     *
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function hiddenField(array | string $parameters): string
    {
        return self::inputField("hidden", $parameters);
    }

    /**
     * Builds HTML IMG tags
     *
     * @param array|string $parameters = [
     *                                 'src'   => '',
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 ]
     * @param bool         $local
     *
     * @return string
     * @throws Exception
     * @throws Url\Exception
     */
    public static function image(
        array | string $parameters = [],
        bool $local = true
    ): string {
        $params = $parameters;
        if (is_string($params)) {
            $params = [$params];
            if (isset($params[1])) {
                $local = (bool)$params[1];
            }
        }

        if (!isset($params["src"])) {
            $params["src"] = $params[0] ?? "";
        }

        /**
         * Use the "url" service if the URI is local
         */
        if (true === $local) {
            $params["src"] = self::getUrlService()
                                 ->getStatic($params["src"])
            ;
        }

        $code = self::renderAttributes("<img", $params);

        /**
         * Check if Doctype is XHTML
         */
        $code .= (self::$documentType > self::HTML5) ? " />" : ">";

        return $code;
    }

    /**
     * Builds an HTML input[type="image"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'name'  => '',
     *                                 'src'   => '',
     *                                 'id'    => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function imageInput(array | string $parameters): string
    {
        return self::inputField("image", $parameters, true);
    }

    /**
     * Builds a SCRIPT[type="javascript"] tag
     *
     * @param array|string $parameters = [
     *                                 'local' => false,
     *                                 'src'   => '',
     *                                 'type'  => 'text/javascript'
     *                                 'rel'   => ''
     *                                 ]
     * @param bool         $local
     *
     * @return string
     * @throws Exception
     * @throws Url\Exception
     */
    public static function javascriptInclude(
        array | string $parameters = [],
        bool $local = true
    ): string {
        $params = $parameters;
        if (is_string($params)) {
            $params = [$params];
        }

        if (isset($params[1])) {
            $local = (bool)$params[1];
        } else {
            if (isset($params["local"])) {
                $local = (bool)$params["local"];

                unset($params["local"]);
            }
        }

        if (
            !isset($params["type"]) &&
            self::$documentType < self::HTML5
        ) {
            $params["type"] = "text/javascript";
        }

        if (!isset($params["src"])) {
            $params["src"] = $params[0] ?? "";
        }

        /**
         * URLs are generated through the "url" service
         */
        if (true === $local) {
            $params["src"] = self::getUrlService()
                                 ->getStatic($params["src"])
            ;
        }

        return self::renderAttributes("<script", $params)
            . "></script>" . PHP_EOL;
    }

    /**
     * Builds an HTML A tag using framework conventions
     *
     * @param array|string $parameters = [
     *                                 'action' => '',
     *                                 'text'   => '',
     *                                 'local'  => false,
     *                                 'query'  => '',
     *                                 'class'  => '',
     *                                 'name'   => '',
     *                                 'href'   => '',
     *                                 'id'     => '',
     *                                 ]
     * @param string|null  $text
     * @param bool         $local
     *
     * @return string
     * @throws Exception
     * @throws Url\Exception
     */
    public static function linkTo(
        array | string $parameters,
        string | null $text = null,
        bool $local = true
    ): string {
        $params = $parameters;
        if (is_string($parameters)) {
            $params = [$parameters, $text, $local];
        }

        if (!isset($params[0])) {
            $action = $params["action"] ?? "";
            if (isset($params["action"])) {
                unset($params["action"]);
            }
        } else {
            $action = $params[0];
        }

        if (!isset($params[1])) {
            $text = $params["text"] ?? "";
            unset($params["text"]);
        } else {
            $text = $params[1];
        }

        if (!isset($params[2])) {
            $local = $params["local"] ?? true;
            unset($params["local"]);
        } else {
            $local = $params[2];
        }

        $query = $params["query"] ?? null;
        if (isset($params["query"])) {
            unset($params["query"]);
        }

        $url            = self::getUrlService();
        $params["href"] = $url->get($action, $query, $local);

        return self::renderAttributes("<a", $params)
            . ">" . $text . "</a>";
    }

    /**
     * Builds an HTML input[type="month"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function monthField(array | string $parameters): string
    {
        return self::inputField("month", $parameters);
    }

    /**
     * Builds an HTML input[type="number"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function numericField(array | string $parameters): string
    {
        return self::inputField("number", $parameters);
    }

    /**
     * Builds a HTML input[type="password"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function passwordField(array | string $parameters): string
    {
        return self::inputField("password", $parameters);
    }

    /**
     * Parses the preload element passed and sets the necessary link headers
     *
     * @param array|string $parameters
     *
     * @return string
     */
    public static function preload(array | string $parameters): string
    {
        $params = $parameters;
        if (is_string($params)) {
            $params = [$params];
        }

        /**
         * Grab the element
         */
        $href = $params[0];

        $container = self::getDI();

        /**
         * Check if we have the response object in the container
         */
        if (true === $container->has("response")) {
            $attributes = $params[1] ?? ["as" => "style"];

            /**
             * href comes wrapped with ''. Remove them
             */
            $response = $container->get("response");
            $link     = new Link(
                "preload",
                str_replace("'", "", $href),
                $attributes
            );
            $header   = "Link: " . (new Header())->serialize([$link]);

            $response->setRawHeader($header);
        }

        return $href;
    }

    /**
     * Prepends a text to current document title
     *
     * @param array|string $title
     *
     * @return void
     */
    public static function prependTitle(array | string $title): void
    {
        if (is_array($title)) {
            self::$documentPrependTitle = $title;
        } else {
            self::$documentPrependTitle[] = $title;
        }
    }

    /**
     * Builds an HTML input[type="radio"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     */
    public static function radioField(array | string $parameters): string
    {
        return self::inputFieldChecked("radio", $parameters);
    }

    /**
     * Builds an HTML input[type="range"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function rangeField(array | string $parameters): string
    {
        return self::inputField("range", $parameters);
    }

    /**
     * Renders parameters keeping order in their HTML attributes
     *
     * @param string $code
     * @param array  $attributes = [
     *                           'rel'    => null,
     *                           'type'   => null,
     *                           'for'    => null,
     *                           'src'    => null,
     *                           'href'   => null,
     *                           'action' => null,
     *                           'id'     => null,
     *                           'name'   => null,
     *                           'value'  => null,
     *                           'class'  => null,
     *                           ]
     *
     * @return string
     * @throws Exception
     */
    public static function renderAttributes(string $code, array $attributes): string
    {
        $order = [
            "rel"    => null,
            "type"   => null,
            "for"    => null,
            "src"    => null,
            "href"   => null,
            "action" => null,
            "id"     => null,
            "name"   => null,
            "value"  => null,
            "class"  => null,
        ];

        $attrs = [];
        foreach ($order as $key => $value) {
            if (isset($attributes[$key])) {
                $attrs[$key] = $attributes[$key];
            }
        }

        foreach ($attributes as $key => $value) {
            if (!isset($attrs[$key])) {
                $attrs[$key] = $value;
            }
        }

        $escaper = self::getEscaper($attributes);

        unset($attrs["escape"]);

        $newCode = $code;
        foreach ($attrs as $key => $value) {
            if (is_string($key) && null !== $value) {
                if (is_array($value) || is_resource($value)) {
                    throw new Exception(
                        "Value at index: '" . $key . "' type: '"
                        . gettype($value) . "' cannot be rendered"
                    );
                }

                $escaped = (null !== $escaper) ? $escaper->attributes($value) : $value;
                $newCode .= " " . $key . "=\"" . $escaped . "\"";
            }
        }

        return $newCode;
    }

    /**
     * Renders the title with title tags. The title is automatically escaped
     *
     * @param bool $prepend
     * @param bool $append
     *
     * @return string
     */
    public static function renderTitle(
        bool $prepend = true,
        bool $append = true
    ): string {
        return "<title>"
            . self::getTitle($prepend, $append)
            . "</title>"
            . PHP_EOL;
    }

    /**
     * Resets the request and internal values to avoid those fields will have
     * any default value.
     *
     * @return void
     * @deprecated Will be removed in 4.0.0
     *
     */
    public static function resetInput(): void
    {
        self::$displayValues          = [];
        self::$documentTitle          = null;
        self::$documentAppendTitle    = [];
        self::$documentPrependTitle   = [];
        self::$documentTitleSeparator = null;
    }

    /**
     * Builds a HTML input[type="search"] tag
     *
     * @param array|string $parameters = [
     *                                 'class' => '',
     *                                 'name'  => '',
     *                                 'id'    => '',
     *                                 'value' => '',
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function searchField(array | string $parameters): string
    {
        return self::inputField("search", $parameters);
    }

    /**
     * Builds a HTML SELECT tag using a Phalcon\Mvc\Model resultset as options
     *
     * @param array|string $parameters = [
     *                                 'id'         => '',
     *                                 'name'       => '',
     *                                 'value'      => '',
     *                                 'useEmpty'   => false,
     *                                 'emptyValue' => '',
     *                                 'emptyText'  => '',
     *                                 ]
     * @param              $data
     *
     * @return string
     * @throws Exception
     */
    public static function select(array | string $parameters, $data = null): string
    {
        return Select::selectField($parameters, $data);
    }

    /**
     * Builds an HTML SELECT tag using a PHP array for options
     *
     * @param array|string $parameters = [
     *                                 'id'         => '',
     *                                 'name'       => '',
     *                                 'value'      => '',
     *                                 'useEmpty'   => false,
     *                                 'emptyValue' => '',
     *                                 'emptyText'  => '',
     *                                 ]
     * @param              $data
     *
     * @return string
     * @throws Exception
     */
    public static function selectStatic(array | string $parameters, $data = null): string
    {
        return Select::selectField($parameters, $data);
    }

    /**
     * Set autoescape mode in generated HTML
     *
     * @param bool $autoescape
     *
     * @return void
     */
    public static function setAutoescape(bool $autoescape): void
    {
        self::$autoEscape = $autoescape;
    }

    /**
     * Sets the dependency injector container.
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public static function setDI(DiInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Assigns default values to generated tags by helpers
     *
     * @param string $id
     * @param mixed  $value
     *
     * @return void
     * @throws Exception
     */
    public static function setDefault(string $id, mixed $value = null): void
    {
        if (null !== $value && true !== is_scalar($value)) {
            throw new Exception(
                "Only scalar values can be assigned to UI components"
            );
        }

        self::$displayValues[$id] = $value;
    }

    /**
     * Assigns default values to generated tags by helpers
     *
     * @param array $values
     * @param bool  $merge
     *
     * @return void
     */
    public static function setDefaults(array $values, bool $merge = false): void
    {
        if (
            true === $merge &&
            is_array(self::$displayValues)
        ) {
            self::$displayValues = array_merge(self::$displayValues, $values);
        } else {
            self::$displayValues = $values;
        }
    }

    /**
     * Set the document type of content
     */
    public static function setDocType(int $doctype): void
    {
        if ($doctype < self::HTML32 || $doctype > self::XHTML5) {
            self::$documentType = self::HTML5;
        } else {
            self::$documentType = $doctype;
        }
    }

    /**
     * Set the title of view content
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle(string $title): void
    {
        self::$documentTitle = $title;
    }

    /**
     * Set the title separator of view content
     *
     * @param string $titleSeparator
     *
     * @return void
     */
    public static function setTitleSeparator(string $titleSeparator): void
    {
        self::$documentTitleSeparator = $titleSeparator;
    }

    /**
     * Builds a LINK[rel="stylesheet"] tag
     *
     * @param array|string|null $parameters
     * @param bool              $local
     *
     * @return string
     * @throws Exception
     * @throws Url\Exception
     */
    public static function stylesheetLink(
        array | string | null $parameters = null,
        bool $local = true
    ): string {
        if (!is_array($parameters)) {
            $params = [$parameters, $local];
        } else {
            $params = $parameters;
        }

        $local = true;
        if (isset($params[1])) {
            $local = (bool)$params[1];
        } else {
            if (isset($params["local"])) {
                $local = (bool)$params["local"];
                unset($params["local"]);
            }
        }

        if (!isset($params["type"])) {
            $params["type"] = "text/css";
        }

        if (!isset($params["href"])) {
            $params["href"] = $params[0] ?? "";
        }

        /**
         * URLs are generated through the "url" service
         */
        if (true === $local) {
            $params["href"] = self::getUrlService()
                                  ->getStatic(
                                      $params["href"]
                                  )
            ;
        }

        $params["rel"] = $params["rel"] ?? "stylesheet";

        $code = self::renderAttributes("<link", $params);

        /**
         * Check if Doctype is XHTML
         */
        $code .= (self::$documentType > self::HTML5) ? " />" : ">";

        return $code . PHP_EOL;
    }

    /**
     * Builds an HTML input[type="submit"] tag
     *
     * @param array|string $parameters
     *
     * @return string
     * @throws Exception
     */
    public static function submitButton(array | string $parameters): string
    {
        return self::inputField("submit", $parameters, true);
    }

    /**
     * Builds a HTML tag
     *
     * @param string       $tagName
     * @param array|string $parameters
     * @param bool         $selfClose
     * @param bool         $onlyStart
     * @param bool         $useEol
     *
     * @return string
     * @throws Exception
     */
    public static function tagHtml(
        string $tagName,
        array | string $parameters = [],
        bool $selfClose = false,
        bool $onlyStart = false,
        bool $useEol = false
    ): string {
        $params = $parameters;
        if (!is_array($parameters)) {
            $params = [$parameters];
        }

        $localCode = self::renderAttributes("<" . $tagName, $params);

        /**
         * Check if Doctype is XHTML
         */
        if (self::$documentType > self::HTML5) {
            $localCode .= (true === $selfClose) ? " />" : ">";
        } else {
            $localCode .= (true === $onlyStart) ? ">" : "></" . $tagName . ">";
        }

        if (true === $useEol) {
            $localCode .= PHP_EOL;
        }

        return $localCode;
    }

    /**
     * Builds a HTML tag closing tag
     *
     * @param string $tagName
     * @param bool   $useEol
     *
     * @return string
     */
    public static function tagHtmlClose(string $tagName, bool $useEol = false): string
    {
        if (true === $useEol) {
            return "</" . $tagName . ">" . PHP_EOL;
        }

        return "</" . $tagName . ">";
    }

    /**
     * Builds an HTML input[type="tel"] tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function telField(array | string $parameters): string
    {
        return self::inputField("tel", $parameters);
    }

    /**
     * Builds an HTML TEXTAREA tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function textArea(array | string $parameters): string
    {
        $params = $parameters;
        if (!is_array($parameters)) {
            $params = [$parameters];
        }

        if (!isset($params[0]) && true === $params["id"]) {
            $params[0] = $params["id"];
        }

        $id = $params[0];
        if (!isset($params["name"])) {
            $params["name"] = $id;
        } else {
            $name = $params["name"] ?? "";
            if (empty($name)) {
                $params["name"] = $id;
            }
        }

        if (!isset($params["id"])) {
            $params["id"] = $id;
        }

        if (isset($params["value"])) {
            $content = $params["value"];

            unset($params["value"]);
        } else {
            $content = self::getValue($id, $params);
        }

        /**
         * PHP 8.x does not allow null to string conversion for internal methods
         */
        if (null === $content) {
            $content = "";
        }

        $code = self::renderAttributes("<textarea", $params);
        $code .= ">" . htmlspecialchars($content) . "</textarea>";

        return $code;
    }

    /**
     * Builds an HTML input[type="text"] tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function textField(array | string $parameters): string
    {
        return self::inputField("text", $parameters);
    }

    /**
     * Builds an HTML input[type="time"] tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function timeField(array | string $parameters): string
    {
        return self::inputField("time", $parameters);
    }

    /**
     * Builds an HTML input[type="url"] tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function urlField(array | string $parameters): string
    {
        return self::inputField("url", $parameters);
    }

    /**
     * Builds an HTML input[type="week"] tag
     *
     * @param array|string $parameters = [
     *                                 'id'    => '',
     *                                 'name'  => '',
     *                                 'value' => '',
     *                                 'class' => ''
     *                                 ]
     *
     * @return string
     * @throws Exception
     */
    public static function weekField(array | string $parameters): string
    {
        return self::inputField("week", $parameters);
    }

    /**
     * Builds generic INPUT tags
     *
     * @param string       $type
     * @param array|string $parameters = {
     *
     * @option string "id"
     * @option string "name"
     * @option string "value"
     * @option string "class"
     * @option string "type"
     * }
     *
     * @param bool         $asValue
     *
     * @return string
     * @throws Exception
     */
    final protected static function inputField(
        string $type,
        array | string $parameters,
        bool $asValue = false
    ): string {
        $params = [];
        $id     = '';

        if (!is_array($parameters)) {
            $params[] = $parameters;
        } else {
            $params = $parameters;
        }

        if (false === $asValue) {
            if (!isset($params[0])) {
                $params[0] = $params["id"];
            } else {
                $id = $params[0];
            }

            if (isset($params["name"])) {
                $name = $params["name"];
                if (empty($name)) {
                    $params["name"] = $id;
                }
            } else {
                $params["name"] = $id;
            }

            /**
             * Automatically assign the id if the name is not an array
             */
            if (is_string($id) && !str_contains($id, "[") && !isset($params["id"])) {
                $params["id"] = $id;
            }

            $params["value"] = self::getValue($id, $params);
        } else {
            /**
             * Use the "id" as value if the user hadn't set it
             */
            if (!isset($params["value"]) && isset($params[0])) {
                $params["value"] = $params[0];
            }
        }

        $params["type"] = $type;
        $code           = self::renderAttributes("<input", $params);

        /**
         * Check if Doctype is XHTML
         */
        $code .= (self::$documentType > self::HTML5) ? " />" : ">";

        return $code;
    }

    /**
     * Builds INPUT tags that implements the checked attribute
     *
     * @param string       $type
     * @param array|string $parameters
     *
     * @return string
     * @throws Exception
     */
    final protected static function inputFieldChecked(
        string $type,
        array | string $parameters
    ): string {
        $params = $parameters;
        if (!is_array($parameters)) {
            $params = [$parameters];
        }

        if (!isset($params[0])) {
            $params[0] = $params["id"];
        }

        $id = $params[0];

        if (!isset($params["name"])) {
            $params["name"] = $id;
        } else {
            $name = $params["name"];

            if (empty($name)) {
                $params["name"] = $id;
            }
        }

        /**
         * Automatically assign the id if the name is not an array
         */
        if (!str_contains($id, "[") && !isset($params["id"])) {
            $params["id"] = $id;
        }

        /**
         * Automatically check inputs
         */
        if (isset($params["value"])) {
            $currentValue = $params["value"];

            unset($params["value"]);

            $value = self::getValue($id, $params);

            if (null !== $value && $currentValue === $value) {
                $params["checked"] = "checked";
            }

            $params["value"] = $currentValue;
        } else {
            $value = self::getValue($id, $params);

            /**
             * Evaluate the value in POST
             */
            if (null !== $value) {
                $params["checked"] = "checked";
            }

            /**
             * Update the value anyway
             */
            $params["value"] = $value;
        }

        $params["type"] = $type;
        $code           = self::renderAttributes("<input", $params);

        /**
         * Check if Doctype is XHTML
         */
        $code .= (self::$documentType > self::HTML5) ? " />" : ">";

        return $code;
    }
}
