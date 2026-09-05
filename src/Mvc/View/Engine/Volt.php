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

namespace Phalcon\Mvc\View\Engine;

use Countable;
use Iterator;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Html\Link\Link;
use Phalcon\Html\Link\Serializer\Header;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Phalcon\Mvc\View\Engine\Volt\Exceptions\InvalidHaystack;
use Phalcon\Mvc\View\Engine\Volt\Exceptions\MacroNotFound;
use Phalcon\Mvc\View\Engine\Volt\Exceptions\MbstringRequired;
use Phalcon\Traits\Php\InfoTrait;

use function array_slice;
use function asort;
use function call_user_func;
use function extract;
use function in_array;
use function is_array;
use function is_object;
use function ob_clean;
use function ob_get_contents;
use function str_replace;
use function strpos;

/**
 * Designer friendly and fast template engine for PHP written in Zephir/C
 */
class Volt extends AbstractEngine implements EventsAwareInterface
{
    use InfoTrait;

    /**
     * @var Compiler|null
     */
    protected Compiler | null $compiler = null;

    /**
     * @var array
     * @phpstan-var array<string, callable>
     */
    protected array $macros = [];

    /**
     * @var array
     * @phpstan-var array<string, mixed>
     */
    protected array $options = [];

    /**
     * Checks if a macro is defined and calls it
     *
     * @param string $name
     * @param array  $arguments
     * @phpstan-param array<array-key, mixed> $arguments
     *
     * @return mixed
     * @throws MacroNotFound
     */
    public function callMacro(string $name, array $arguments = [])
    {
        if (!isset($this->macros[$name])) {
            throw new MacroNotFound($name);
        }

        return call_user_func($this->macros[$name], $arguments);
    }

    /**
     * Performs a string conversion
     *
     * @param string $text
     * @param string $from
     * @param string $to
     *
     * @return string
     * @throws MbstringRequired
     */
    public function convertEncoding(string $text, string $from, string $to): string
    {
        if (!function_exists("mb_convert_encoding")) {
            throw new MbstringRequired();
        }

        // PHP 8 throws on an unknown encoding, so the result is a string.
        /** @var string $converted */
        $converted = mb_convert_encoding($text, $to, $from);

        return $converted;
    }

    /**
     * Returns the Volt's compiler
     *
     * @return Compiler
     */
    public function getCompiler(): Compiler
    {
        if (!is_object($this->compiler)) {
            $compiler = new Compiler($this->view);

            /**
             * Pass the IoC to the compiler only of it's an object
             */
            if (null !== $this->container) {
                $compiler->setDi($this->container);
            }

            /**
             * Pass the options to the compiler only if they're an array
             */
            $compiler->setOptions($this->options);

            $this->compiler = $compiler;
        }

        return $this->compiler;
    }

    /**
     * Return Volt's options
     *
     * @return array
     * @phpstan-return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Checks if the needle is included in the haystack
     *
     * @param mixed $needle
     * @param mixed $haystack
     *
     * @return bool
     */
    public function isIncluded(mixed $needle, mixed $haystack): bool
    {
        if (null === $needle) {
            $needle = "";
        }

        if (is_array($haystack)) {
            return in_array($needle, $haystack);
        }

        if (is_string($haystack)) {
            // A string haystack needs a string needle.
            /** @var string $needle */
            if ($this->phpFunctionExists("mb_strpos")) {
                return mb_strpos($haystack, $needle) !== false;
            }

            return strpos($haystack, $needle) !== false;
        }

        throw new InvalidHaystack();
    }

    /**
     * Length filter. If an array/object is passed a count is performed otherwise a strlen/mb_strlen
     *
     * @param mixed $item
     * @phpstan-param array<array-key, mixed>|Countable|string|null $item
     *
     * @return int
     */
    public function length(mixed $item): int
    {
        if (null === $item) {
            $item = "";
        }

        if (is_object($item) || is_array($item)) {
            return count($item);
        }

        return mb_strlen($item);
    }

    /**
     * Parses the preload element passed and sets the necessary link headers
     *
     * @phpstan-param array{
     *     0?: string,
     *     1?: array<string, array<string>|bool|float|int|string|null>
     * }|string $parameters
     *
     * @todo find a better way to handle this
     */
    public function preload(mixed $parameters): string
    {
        $params = [];

        if (!is_array($parameters)) {
            $params = [$parameters];
        } else {
            $params = $parameters;
        }

        /**
         * Grab the element
         */
        $href = $params[0] ?? '';

        /**
         * Check if we have the response object in the container
         */
        /**
         * Check if we have the response object in the container. Without a
         * container the href is given back as it is.
         */
        $container = $this->container;

        if (null !== $container && $container->has("response")) {
            if (isset($params[1])) {
                $attributes = $params[1];
            } else {
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
     * Renders a view using the template engine
     *
     * @param string $path
     * @param mixed  $params
     * @param bool   $mustClean
     *
     * TODO: Make params array
     *
     * @return void|null
     * @throws Volt\Exception
     * @throws EventsException
     */
    public function render(string $path, mixed $params, bool $mustClean = false)
    {
        if ($mustClean) {
            ob_clean();
        }

        /**
         * The compilation process is done by Phalcon\Mvc\View\Engine\Volt\Compiler
         */
        $compiler = $this->getCompiler();

        if (false === $this->fireManagerEvent("view:beforeCompile")) {
            return null;
        }

        $compiler->compile($path);

        if (false === $this->fireManagerEvent("view:afterCompile")) {
            return null;
        }

        $compiledTemplatePath = $compiler->getCompiledTemplatePath();

        /**
         * Include the compiled template inside a closure whose only locals
         * carry reserved names, so a parameter cannot replace the file path.
         * The closure is bound to $this, so templates keep using it.
         */
        $include = function (string $__path, mixed $__params): void {
            if (is_array($__params)) {
                extract($__params, EXTR_SKIP);
            }

            require $__path;
        };

        $include->call($this, $compiledTemplatePath, $params);

        if ($mustClean) {
            // The view starts the buffer before it calls the engine.
            /** @var string $contents */
            $contents = ob_get_contents();

            $this->view->setContent($contents);
        }
    }

    /**
     * Set Volt's options
     *
     * @param array $options
     * @phpstan-param array<string, mixed> $options
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Extracts a slice from a string/array/traversable object value
     *
     * @param mixed $value
     * @phpstan-param array<array-key, mixed>|string|(Countable&Iterator<array-key, mixed>) $value
     * @param int        $start
     * @param mixed|null $end
     * @phpstan-param int|null $end
     *
     * @return array|string
     * @phpstan-return array<array-key, mixed>|string
     */
    public function slice(mixed $value, int $start = 0, mixed $end = null)
    {
        /**
         * Objects must implement a Traversable interface
         */
        if (is_object($value)) {
            if (null === $end) {
                $end = count($value) - 1;
            }

            $position = 0;
            $slice    = [];

            $value->rewind();

            while ($value->valid()) {
                if ($position >= $start && $position <= $end) {
                    $slice[] = $value->current();
                }

                $value->next();

                $position++;
            }

            return $slice;
        }

        /**
         * Calculate the slice length
         */
        $length = null;
        if (null !== $end) {
            $length = ($end - $start) + 1;
        }

        /**
         * Use array_slice on arrays
         */
        if (is_array($value)) {
            return array_slice($value, $start, $length);
        }

        /**
         * Use mb_substr if available
         */
        return mb_substr($value, $start, $length);
    }

    /**
     * Sorts an array
     *
     * @param array $value
     * @phpstan-param array<array-key, mixed> $value
     *
     * @return array
     * @phpstan-return array<array-key, mixed>
     */
    public function sort(array $value): array
    {
        asort($value);

        return $value;
    }
}
