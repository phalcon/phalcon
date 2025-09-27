<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by Nyholm/psr7 and Laminas
 *
 * @link    https://github.com/Nyholm/psr7
 * @license https://github.com/Nyholm/psr7/blob/master/LICENSE
 * @link    https://github.com/laminas/laminas-diactoros
 * @license https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Http\Message;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Traits\Helper\Str\StartsWithTrait;

use function array_keys;
use function explode;
use function implode;
use function ltrim;
use function mb_strtolower;
use function parse_url;
use function preg_match;
use function preg_replace;
use function rawurlencode;
use function str_replace;
use function str_split;
use function strtolower;
use function substr;

/**
 * Uri
 *
 * @property string   $fragment
 * @property string   $host
 * @property string   $pass
 * @property int|null $port
 * @property string   $query
 * @property string   $scheme
 * @property string   $userInfo
 */
class Uri extends AbstractCommon implements UriInterface
{
    use StartsWithTrait;

    /**
     * Sub-delimiters used in user info, query strings and fragments.
     *
     * @const string
     */
    private const CHAR_SUB_DELIMS = "!\$&\'\(\)\*\+,;=";

    /**
     * Unreserved characters used in user info, paths, query strings, and
     * fragments.
     *
     * @const string
     */
    private const CHAR_UNRESERVED = "a-zA-Z0-9_\-\.~\pL";

    /**
     * Returns the fragment of the URL
     *
     * @var string
     */
    protected string $fragment = "";

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @var string
     */
    protected string $host = "";

    /**
     * Returns the path of the URL
     *
     * @var string
     */
    protected string $path = "";

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard
     * port used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @var int|null
     */
    protected int | null $port = null;

    /**
     * Returns the query of the URL
     *
     * @var string
     */
    protected string $query = "";

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @var string
     */
    protected string $scheme = "";

    /**
     * @var string
     */
    protected string $userInfo = "";

    /**
     * Uri constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri = "")
    {
        if (null !== $uri) {
            $urlParts = $this->phpParseUrl($uri);

            if (false === $urlParts) {
                throw new InvalidArgumentException("The URI cannot be parsed");
            }

            $this->fragment = $this->filterFragment(
                $urlParts["fragment"] ?? ""
            );
            $this->host     = strtolower($urlParts["host"] ?? "");
            $this->path     = $this->filterPath($urlParts["path"] ?? "");
            $this->port     = $this->filterPort($urlParts["port"] ?? null);
            $this->query    = $this->filterQuery($urlParts["query"] ?? "");
            $this->scheme   = $this->filterScheme($urlParts["scheme"] ?? "");
            $this->userInfo = $this->filterUserInfo($urlParts["user"] ?? "");

            if (isset($urlParts["pass"])) {
                $this->userInfo .= ":" . $this->filterUserInfo(
                    $urlParts["pass"]
                );
            }
        }
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters
     *
     * @return string
     */
    public function __toString(): string
    {
        $authority = $this->getAuthority();
        $path      = $this->path;

        /**
         * The path can be concatenated without delimiters. But there are two
         * cases where the path has to be adjusted to make the URI reference
         * valid as PHP does not allow to throw an exception in __toString():
         *   - If the path is rootless and an authority is present, the path
         *     MUST be prefixed by "/".
         *   - If the path is starting with more than one "/" and no authority
         *     is present, the starting slashes MUST be reduced to one.
         */
        if ("" !== $path) {
            if (
                !str_starts_with($path, "/") &&
                "" !== $authority
            ) {
                // If the path is rootless and an authority is present,
                // the path MUST be prefixed by "/"
                $path = "/" . $path;
            } elseif (
                "/" === substr($path, 1, 1) &&
                "" === $authority
            ) {
                // If the path is starting with more than one "/" and no
                // authority is present, the starting slashes MUST be reduced
                // to one.
                $path = "/" . ltrim($path, "/");
            }
        }

        return $this->checkValue($this->scheme, "", ":")
            . $this->checkValue($authority, "//")
            . $path
            . $this->checkValue($this->query, "?")
            . $this->checkValue($this->fragment, "#");
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * @return string
     */
    public function getAuthority(): string
    {
        /**
         * If no authority information is present, this method MUST return an
         * empty string.
         */
        if ("" === $this->host) {
            return "";
        }

        /**
         * The authority syntax of the URI is:
         *
         * [user-info@]host[:port]
         */
        $authority = $this->host;
        if ("" !== $this->userInfo) {
            $authority = $this->userInfo . "@" . $authority;
        }

        /**
         * If the port component is not set or is the standard port for the
         * current scheme, it SHOULD NOT be included.
         */
        if (null !== $this->port) {
            $authority .= ":" . $this->port;
        }

        return $authority;
    }

    /**
     * Returns the fragment of the URL
     *
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Returns the path of the URL
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard
     * port used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return int|null
     */
    public function getPort(): int | null
    {
        return $this->port;
    }

    /**
     * Returns the query of the URL
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }


    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment
     *
     * @return Uri
     */
    public function withFragment(string $fragment): UriInterface
    {
        return $this->cloneInstance(
            $this->filterFragment($fragment),
            "fragment"
        );
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host
     *
     * @return Uri
     * @throws InvalidArgumentException for invalid hostnames.
     */
    public function withHost(string $host): UriInterface
    {
        return $this->cloneInstance(mb_strtolower($host), "host");
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If an HTTP path is intended to be host-relative rather than path-relative
     * then it must begin with a slash ("/"). HTTP paths not starting with a
     * slash are assumed to be relative to some base path known to the
     * application or consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path
     *
     * @return Uri
     * @throws InvalidArgumentException for invalid paths.
     */
    public function withPath(string $path): UriInterface
    {
        if (
            str_contains($path, "?") ||
            str_contains($path, "#")
        ) {
            throw new InvalidArgumentException(
                "Path cannot contain a query string or fragment"
            );
        }

        return $this->cloneInstance($this->filterPath($path), "path");
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param int|null $port
     *
     * @return Uri
     * @throws InvalidArgumentException for invalid ports.
     */
    public function withPort(int | null $port): UriInterface
    {
        return $this->cloneInstance(
            $this->filterPort($port),
            "port"
        );
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query
     *
     * @return Uri
     * @throws InvalidArgumentException for invalid query strings.
     */
    public function withQuery(string $query): UriInterface
    {
        if (str_contains($query, "#")) {
            throw new InvalidArgumentException(
                "Query cannot contain a URI fragment"
            );
        }

        return $this->cloneInstance($this->filterQuery($query), "query");
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme
     *
     * @return Uri
     * @throws InvalidArgumentException for invalid schemes.
     * @throws InvalidArgumentException for unsupported schemes.
     */
    public function withScheme(string $scheme): UriInterface
    {
        return $this->cloneInstance($this->filterScheme($scheme), "scheme");
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param string      $user
     * @param string|null $password
     *
     * @return Uri
     */
    public function withUserInfo(
        string $user,
        string | null $password = null
    ): UriInterface {
        $userInfo = $this->filterUserInfo($user);
        if (null !== $password) {
            $userInfo .= ":" . $this->filterUserInfo($password);
        }

        return $this->cloneInstance($userInfo, "userInfo");
    }

    /**
     * Proxy method for parse_url for tests
     *
     * @param string $url
     *
     * @return array|false|int|string|null
     */
    protected function phpParseUrl(string $url)
    {
        return parse_url($url);
    }

    /**
     * If the value passed is empty it returns it prefixed and suffixed with
     * the passed parameters
     *
     * @param string $value
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    private function checkValue(
        string $value,
        string $prefix = "",
        string $suffix = ""
    ): string {
        if ("" !== $value) {
            $value = $prefix . $value . $suffix;
        }

        return $value;
    }

    /**
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @param string $fragment
     *
     * @return string
     */
    private function filterFragment(string $fragment): string
    {
        if ("" !== $fragment && str_starts_with($fragment, "#")) {
            $fragment = "%23" . substr($fragment, 1);
        }

        return $this->filterQueryOrFragment($fragment);
    }

    /**
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     *
     * @param string $path
     *
     * @return string The URI path.
     */
    private function filterPath(string $path): string
    {
        $path = $this->filterString(
            "/(?:[^"
            . self::CHAR_UNRESERVED
            . ")(:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u",
            $path
        );

        if ("" === $path || !str_starts_with($path, "/")) {
            return $path;
        }

        return "/" . ltrim($path, "/");
    }

    /**
     * Filters the port
     *
     * @param int | null $port
     *
     * @return int|null
     */
    private function filterPort(int | null $port): int | null
    {
        if (null === $port) {
            return null;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(
                "Invalid port specified. (Valid range 1-65535)"
            );
        }

        $schemes = [
            "http"  => 80,
            "https" => 443,
        ];

        if (
            isset($schemes[$this->scheme]) &&
            $port === $schemes[$this->scheme]
        ) {
            return null;
        }

        return $port;
    }

    /**
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     *
     * @param string $query
     *
     * @return string The URI query string.
     */
    private function filterQuery(string $query): string
    {
        if ("" === $query) {
            return "";
        }

        $query = ltrim($query, "?");
        $parts = explode("&", $query);

        foreach ($parts as $index => $part) {
            $split = $this->splitQueryValue($part);
            if (null === $split[1]) {
                $parts[$index] = $this->filterQueryOrFragment($split[0]);
                continue;
            }

            $parts[$index] = $this->filterQueryOrFragment($split[0])
                . "="
                . $this->filterQueryOrFragment($split[1]);
        }

        return implode("&", $parts);
    }

    /**
     * Filters a string (query or fragment) based on a pattern
     *
     * @param string $value
     *
     * @return string
     */
    private function filterQueryOrFragment(string $value): string
    {
        return $this->filterString(
            "/(?:[^"
            . self::CHAR_UNRESERVED
            . self::CHAR_SUB_DELIMS
            . "%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u",
            $this->filterUtf8($value)
        );
    }

    /**
     * Filters the passed scheme - only allowed schemes
     *
     * @param string $scheme
     *
     * @return string
     */
    private function filterScheme(string $scheme): string
    {
        $filtered = preg_replace("#:(//)?$#", "", mb_strtolower($scheme));
        $schemes  = [
            "http"  => 1,
            "https" => 1,
        ];

        if ("" === $filtered) {
            return "";
        }

        if (!isset($schemes[$filtered])) {
            throw new InvalidArgumentException(
                "Unsupported scheme [" . $filtered . "]. " .
                "Scheme must be one of [" .
                implode(", ", array_keys($schemes)) . "]"
            );
        }

        return $filtered;
    }

    /**
     * Filters a string based on a pattern
     *
     * @param string $pattern
     * @param string $value
     *
     * @return string
     */
    private function filterString(string $pattern, string $value): string
    {
        $matches = [];
        $value   = $this->filterUtf8($value);
        /**
         * Because preg_match_callback does not work in Zephir (for now),
         * replace the spaces with %20
         */
        $value  = str_replace(" ", "%20", $value);
        $result = preg_match($pattern, $value, $matches);

        if (false === $result) {
            return $value;
        }

        foreach ($matches as $match) {
            $value = str_replace($match, rawurlencode($match), $value);
        }

        return $value;
    }

    /**
     * Filter userInfo data
     *
     * @param string $value
     *
     * @return string
     */
    private function filterUserInfo(string $value): string
    {
        // Note the addition of `%` to initial charset; this allows `|` portion
        // to match and thus prevent double-encoding.
        return $this->filterString(
            "/(?:[^%"
            . self::CHAR_UNRESERVED
            . self::CHAR_SUB_DELIMS
            . "]+|%(?![A-Fa-f0-9]{2}))/u",
            $value
        );
    }

    /**
     * Check and filter invalid UTF-8 characters
     *
     * @param string $value
     *
     * @return string
     */
    private function filterUtf8(string $value): string
    {
        // check if given string contains only valid UTF-8 characters
        if (preg_match("//u", $value)) {
            return $value;
        }

        $characters = str_split($value);
        foreach ($characters as $index => $character) {
            if (!preg_match("//u", $character)) {
                $characters[$index] = rawurlencode($character);
            }
        }

        return implode("", $characters);
    }

    /**
     * @param string $element
     *
     * @return array
     */
    private function splitQueryValue(string $element): array
    {
        $data = explode("=", $element, 2);
        if (!isset($data[1])) {
            $data[] = null;
        }

        return $data;
    }
}
