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
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Url\UrlInterface;
use Phalcon\Support\Helper\Str\Friendly;
use Phalcon\Tag\Exception;
use Phalcon\Tag\Select;
use Stringable;

use function array_merge;
use function array_reverse;
use function htmlspecialchars;
use function implode;
use function is_array;
use function is_resource;
use function is_scalar;
use function is_string;
use function method_exists;
use function preg_replace;
use function str_replace;

use const PHP_EOL;

/**
 * Phalcon\Tag is designed to simplify building of HTML tags.
 * It provides a set of helpers to generate HTML in a dynamic way.
 * This component is a class that you can extend to add more helpers.
 *
 * @phpstan-type tag_parameters array<array-key, mixed>
 * @phpstan-type tag_attributes array<array-key, mixed>
 * @phpstan-type tag_display_values array<array-key, scalar|null>
 * @phpstan-type tag_title_parts array<array-key, string>
 * @phpstan-type tag_select_data array<array-key, mixed>
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

    protected static bool $autoEscape = true;
    protected static DiInterface | null $container = null;
    /**
     * @phpstan-var tag_display_values
     */
    protected static array $displayValues = [];
    /**
     * @phpstan-var tag_title_parts
     */
    protected static array $documentAppendTitle = [];
    /**
     * @phpstan-var tag_title_parts
     */
    protected static array $documentPrependTitle = [];
    protected static string | null $documentTitle = "";
    protected static string | null $documentTitleSeparator = "";
    protected static int $documentType = 11;
    protected static EscaperInterface | null $escaperService = null;
    protected static UrlInterface | null $urlService = null;

    /**
     * Appends a text to current document title
     *
     * @phpstan-param tag_title_parts|string $title
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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function checkField(array | string $parameters): string
    {
        return self::inputFieldChecked("checkbox", $parameters);
    }

    /**
     * Builds an HTML input[type="color"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function colorField(array | string $parameters): string
    {
        return self::inputField("color", $parameters);
    }

    /**
     * Builds an HTML input[type="date"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function dateField(array | string $parameters): string
    {
        return self::inputField("date", $parameters);
    }

    /**
     * Builds an HTML input[type="datetime"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function dateTimeField(array | string $parameters): string
    {
        return self::inputField("datetime", $parameters);
    }

    /**
     * Builds an HTML input[type="datetime-local"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function dateTimeLocalField(array | string $parameters): string
    {
        return self::inputField("datetime-local", $parameters);
    }

    /**
     * Alias of Phalcon\Tag::setDefault()
     */
    public static function displayTo(string $id, mixed $value): void
    {
        self::setDefault($id, $value);
    }

    /**
     * Builds an HTML input[type="email"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function emailField(array | string $parameters): string
    {
        return self::inputField("email", $parameters);
    }

    /**
     * Builds an HTML close FORM tag
     */
    public static function endForm(): string
    {
        return "</form>";
    }

    /**
     * Builds an HTML input[type="file"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     *     'value' => ''
     * ]
     */
    public static function fileField(array | string $parameters): string
    {
        return self::inputField("file", $parameters);
    }

    /**
     * Builds an HTML FORM tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'method' => 'post',
     *     'action' => '',
     *     'parameters' => '',
     *     'name' => '',
     *     'class' => '',
     *     'id' => ''
     * ]
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
                          ->get(self::toStringValue($paramsAction))
            ;
        }

        /**
         * Check for extra parameters
         */
        if (isset($params["parameters"])) {
            $action .= "?" . self::toStringValue($params["parameters"]);
        }

        if (!empty($action)) {
            $params["action"] = $action;
        }

        return self::renderAttributes("<form", $params) . ">";
    }

    /**
     * Converts texts into URL-friendly titles
     *
     * @phpstan-param array<array-key, string>|string $replace
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
     */
    public static function getDI(): DiInterface
    {
        $container = self::$container;

        if (null === $container) {
            $container = Di::getDefault();

            if (!($container instanceof DiInterface)) {
                throw new Exception(
                    "A dependency injection container is required"
                );
            }

            self::$container = $container;
        }

        return $container;
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
     * @phpstan-param tag_parameters $parameters
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
     */
    public static function getEscaperService(): EscaperInterface
    {
        $escaper = self::$escaperService;

        if (null === $escaper) {
            /** @var EscaperInterface $escaper */
            $escaper              = self::getDI()->getShared("escaper");
            self::$escaperService = $escaper;
        }

        return $escaper;
    }

    /**
     * Gets the current document title. The title will be automatically escaped.
     */
    public static function getTitle(
        bool $prepend = true,
        bool $append = true
    ): string {
        $escaper                = self::getEscaperService();
        $items                  = [];
        $output                 = "";
        $documentTitle          = $escaper->html(self::$documentTitle ?? "");
        $documentTitleSeparator = $escaper->html(
            self::$documentTitleSeparator ?? ""
        );

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
     */
    public static function getTitleSeparator(): string
    {
        return self::$documentTitleSeparator ?? "";
    }

    /**
     * Returns a URL service from the default DI
     */
    public static function getUrlService(): UrlInterface
    {
        $url = self::$urlService;

        if (null === $url) {
            /** @var UrlInterface $url */
            $url              = self::getDI()->getShared("url");
            self::$urlService = $url;
        }

        return $url;
    }

    /**
     * Every helper calls this function to check whether a component has a
     * predefined value using Phalcon\Tag::setDefault() or value from $_POST
     *
     * @phpstan-param tag_parameters $parameters
     */
    public static function getValue(int | string $name, array $parameters = []): mixed
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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function hiddenField(array | string $parameters): string
    {
        return self::inputField("hidden", $parameters);
    }

    /**
     * Builds HTML IMG tags
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'src' => '',
     *     'class' => '',
     *     'id' => '',
     *     'name' => ''
     * ]
     */
    public static function image(
        array | string $parameters = [],
        bool $local = true
    ): string {
        $params = is_array($parameters) ? $parameters : [$parameters];

        /**
         * A positional index 1 overrides the method argument, matching
         * javascriptInclude() and stylesheetLink().
         */
        if (isset($params[1])) {
            $local = (bool)$params[1];
        }

        if (!isset($params["src"])) {
            $params["src"] = $params[0] ?? "";
        }

        /**
         * Use the "url" service if the URI is local
         */
        if (true === $local) {
            $params["src"] = self::getStaticUrl($params["src"]);
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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => ''
     * ]
     */
    public static function imageInput(array | string $parameters): string
    {
        return self::inputField("image", $parameters, true);
    }

    /**
     * Builds a SCRIPT[type="javascript"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'local' => false,
     *     'src' => '',
     *     'type' => 'text/javascript'
     *     'rel' => ''
     * ]
     */
    public static function javascriptInclude(
        array | string $parameters = [],
        bool $local = true
    ): string {
        $params = is_array($parameters) ? $parameters : [$parameters];

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
            $params["src"] = self::getStaticUrl($params["src"]);
        }

        return self::renderAttributes("<script", $params)
            . "></script>" . PHP_EOL;
    }

    /**
     * Builds an HTML A tag using framework conventions
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'action' => '',
     *     'text' => '',
     *     'local' => false,
     *     'query' => '',
     *     'class' => '',
     *     'name' => '',
     *     'href' => '',
     *     'id' => ''
     * ]
     */
    public static function linkTo(
        array | string $parameters,
        string | null $text = null,
        bool $local = true
    ): string {
        $params = is_string($parameters)
            ? [$parameters, $text, $local]
            : $parameters;

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
        $params["href"] = $url->get(
            self::toStringValue($action),
            is_array($query) ? $query : null,
            (bool)$local
        );

        return self::renderAttributes("<a", $params)
            . ">" . self::toStringValue($text) . "</a>";
    }

    /**
     * Builds an HTML input[type="month"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function monthField(array | string $parameters): string
    {
        return self::inputField("month", $parameters);
    }

    /**
     * Builds an HTML input[type="number"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function numericField(array | string $parameters): string
    {
        return self::inputField("number", $parameters);
    }

    /**
     * Builds a HTML input[type="password"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function passwordField(array | string $parameters): string
    {
        return self::inputField("password", $parameters);
    }

    /**
     * Parses the preload element passed and sets the necessary link headers
     *
     * @phpstan-param tag_parameters|string $parameters
     */
    public static function preload(array | string $parameters): string
    {
        $params = is_array($parameters) ? $parameters : [$parameters];

        /**
         * Grab the element
         */
        $href = self::toStringValue($params[0]);

        $container = self::getDI();

        /**
         * Check if we have the response object in the container
         */
        if (true === $container->has("response")) {
            $attributes = $params[1] ?? ["as" => "style"];
            if (!is_array($attributes)) {
                $attributes = ["as" => "style"];
            }

            /**
             * href comes wrapped with ''. Remove them
             */
            /** @var ResponseInterface $response */
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
     * @phpstan-param tag_title_parts|string $title
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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function radioField(array | string $parameters): string
    {
        return self::inputFieldChecked("radio", $parameters);
    }

    /**
     * Builds an HTML input[type="range"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function rangeField(array | string $parameters): string
    {
        return self::inputField("range", $parameters);
    }

    /**
     * Renders parameters keeping order in their HTML attributes
     *
     * @param array $attributes = [
     *     'rel' => null,
     *     'type' => null,
     *     'for' => null,
     *     'src' => null,
     *     'href' => null,
     *     'action' => null,
     *     'id' => null,
     *     'name' => null,
     *     'value' => null,
     *     'class' => null
     * ]
     *
     * @phpstan-param tag_attributes $attributes
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

                $escaped = (null !== $escaper)
                    ? $escaper->attributes(self::toStringValue($value))
                    : self::toStringValue($value);

                /**
                 * The key is an attribute name. Remove the characters that end
                 * a name so a crafted key cannot add more attributes or close
                 * the tag.
                 */
                $key = preg_replace('~[\s/=<>"\']~', '', $key);

                $newCode .= " " . $key . "=\"" . $escaped . "\"";
            }
        }

        return $newCode;
    }

    /**
     * Renders the title with title tags. The title is automatically escaped
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
     * @deprecated Will be removed in 4.0.0
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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'class' => '',
     *     'name' => '',
     *     'src' => '',
     *     'id' => '',
     *     'value' => ''
     * ]
     */
    public static function searchField(array | string $parameters): string
    {
        return self::inputField("search", $parameters);
    }

    /**
     * Builds a HTML SELECT tag using a Phalcon\Mvc\Model resultset as options
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'useEmpty' => false,
     *     'emptyValue' => '',
     *     'emptyText' => '',
     * ]
     */
    public static function select(array | string $parameters, mixed $data = null): string
    {
        return Select::selectField($parameters, $data);
    }

    /**
     * Builds an HTML SELECT tag using a PHP array for options
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'useEmpty' => false,
     *     'emptyValue' => '',
     *     'emptyText' => '',
     * ]
     */
    public static function selectStatic(array | string $parameters, mixed $data = null): string
    {
        return Select::selectField($parameters, $data);
    }

    /**
     * Set autoescape mode in generated HTML
     */
    public static function setAutoescape(bool $autoescape): void
    {
        self::$autoEscape = $autoescape;
    }

    /**
     * Assigns default values to generated tags by helpers
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
     * @phpstan-param tag_display_values $values
     */
    public static function setDefaults(array $values, bool $merge = false): void
    {
        if (true === $merge) {
            self::$displayValues = array_merge(self::$displayValues, $values);
        } else {
            self::$displayValues = $values;
        }
    }

    /**
     * Sets the dependency injector container.
     */
    public static function setDI(DiInterface $container): void
    {
        self::$container = $container;
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
     */
    public static function setTitle(string $title): void
    {
        self::$documentTitle = $title;
    }

    /**
     * Set the title separator of view content
     */
    public static function setTitleSeparator(string $titleSeparator): void
    {
        self::$documentTitleSeparator = $titleSeparator;
    }

    /**
     * Builds a LINK[rel="stylesheet"] tag
     *
     * @phpstan-param tag_parameters|string|null $parameters
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
            $params["href"] = self::getStaticUrl($params["href"]);
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
     * @phpstan-param tag_parameters|string $parameters
     */
    public static function submitButton(array | string $parameters): string
    {
        return self::inputField("submit", $parameters, true);
    }

    /**
     * Builds a HTML tag
     *
     * @phpstan-param tag_parameters|string $parameters
     */
    public static function tagHtml(
        string $tagName,
        array | string $parameters = [],
        bool $selfClose = false,
        bool $onlyStart = false,
        bool $useEol = false
    ): string {
        $params = is_array($parameters) ? $parameters : [$parameters];

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
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function telField(array | string $parameters): string
    {
        return self::inputField("tel", $parameters);
    }

    /**
     * Builds an HTML TEXTAREA tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function textArea(array | string $parameters): string
    {
        $params = is_array($parameters) ? $parameters : [$parameters];

        if (!isset($params[0]) && true === ($params["id"] ?? null)) {
            $params[0] = $params["id"];
        }

        $id = self::toStringValue($params[0]);
        if (!isset($params["name"])) {
            $params["name"] = $id;
        } else {
            $name = $params["name"];
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
        $code .= ">" . htmlspecialchars(self::toStringValue($content))
            . "</textarea>";

        return $code;
    }

    /**
     * Builds an HTML input[type="text"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function textField(array | string $parameters): string
    {
        return self::inputField("text", $parameters);
    }

    /**
     * Builds an HTML input[type="time"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function timeField(array | string $parameters): string
    {
        return self::inputField("time", $parameters);
    }

    /**
     * Builds an HTML input[type="url"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function urlField(array | string $parameters): string
    {
        return self::inputField("url", $parameters);
    }

    /**
     * Builds an HTML input[type="week"] tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => ''
     * ]
     */
    public static function weekField(array | string $parameters): string
    {
        return self::inputField("week", $parameters);
    }

    /**
     * Resolves a static (asset) URL through the `url` service.
     *
     * `getStatic()` lives on Phalcon\Mvc\Url but is absent from
     * Phalcon\Mvc\Url\UrlInterface, which is what getUrlService() is typed
     * to return. A service that does not carry it falls back to `get()`
     * rather than aborting the helper.
     */
    final protected static function getStaticUrl(mixed $uri): string
    {
        $url = self::getUrlService();

        if (!is_string($uri) && !is_array($uri)) {
            $uri = self::toStringValue($uri);
        }

        if (method_exists($url, "getStatic")) {
            return self::toStringValue($url->getStatic($uri));
        }

        return $url->get($uri);
    }

    /**
     * Builds generic INPUT tags
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'class' => '',
     *     'type' => ''
     * ]
     */
    final protected static function inputField(
        string $type,
        array | string $parameters,
        bool $asValue = false
    ): string {
        $params = [];

        if (!is_array($parameters)) {
            $params[] = $parameters;
        } else {
            $params = $parameters;
        }

        if (false === $asValue) {
            if (!isset($params[0])) {
                $params[0] = $params["id"] ?? "";
            }

            $id = self::toStringValue($params[0]);

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
            if (!str_contains($id, "[") && !isset($params["id"])) {
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
     * @phpstan-param tag_parameters|string $parameters
     */
    final protected static function inputFieldChecked(
        string $type,
        array | string $parameters
    ): string {
        $params = is_array($parameters) ? $parameters : [$parameters];

        if (!isset($params[0])) {
            $params[0] = $params["id"] ?? "";
        }

        $id = self::toStringValue($params[0]);

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

    /**
     * Reduces an arbitrary helper value to the string a tag attribute, id or
     * URI needs. Parameter bags are user supplied, so a value that cannot be
     * expressed as a string - an array, an object without `__toString()` -
     * reads back as an empty string rather than aborting the helper.
     */
    final protected static function toStringValue(mixed $value): string
    {
        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return "";
    }
}
