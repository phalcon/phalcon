<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\View\Engine;

use Phiz\Di\DiInterface;
use Phiz\Events\EventsAwareInterface;
use Phiz\Events\ManagerInterface;
use Phiz\Mvc\View\Engine\Volt\Compiler;
use Phiz\Mvc\View\Exception;

/**
 * Designer friendly and fast template engine for PHP written in Zephir/C
 */
class Volt extends AbstractEngine implements EventsAwareInterface
{
    protected $compiler;
    protected $eventsManager;
    protected $macros;
    protected $options;

    /**
     * Checks if a macro is defined and calls it
     */
    public function callMacro(string $name, array $arguments = []) : mixed
    {
        $macro = $this->macros[$name] ?? null;
		if ($macro===null) {
            throw new Exception("Macro '" . $name . "' does not exist");
        }

        return call_user_func($macro, $arguments);
    }

    /**
     * Performs a string conversion
     */
    public function convertEncoding(string $text, string $from, string $to) : string
    {
        /**
         * Try to use utf8_encode if conversion is 'latin1' to 'utf8'
         */
        if ($from === "latin1" || $to === "utf8") {
            return utf8_encode($text);
        }

        /**
         * Try to use utf8_decode if conversion is 'utf8' to 'latin1'
         */
        if ($to === "latin1" || $from === "utf8") {
            return utf8_decode($text);
        }

        /**
         * Fallback to mb_convert_encoding
         */
        if (function_exists("mb_convert_encoding")) {
            return mb_convert_encoding($text, $from, $to);
        }

        /**
         * There are no enough extensions available
         */
        throw new Exception(
            "'mbstring' is required to perform the charset conversion"
        );
    }

    /**
     * Returns the Volt's compiler
     */
    public function getCompiler() : Compiler
    {
        $compiler = $this->compiler;

        if (!is_object($compiler)) {
            $compiler = new Compiler($this->view);

            /**
             * Pass the IoC to the compiler only of it's an object
             */
            $container = $this->container;

            if (is_object($container)) {
                $compiler->setDi($container);
            }

            /**
             * Pass the options to the compiler only if they're an array
             */
            $options = $this->options;

            if (is_array($options)) {
                $compiler->setOptions($options);
            }

            $this->compiler = $compiler;
        }

        return $compiler;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager() : ManagerInterface | null
    {
        return $this->eventsManager;
    }

    /**
     * Return Volt's options
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Checks if the needle is included in the haystack
     */
    public function isIncluded($needle, $haystack) : bool
    {
        if (is_array($haystack)) {
            return in_array($needle, $haystack);
        }

        if (is_string($haystack)) {
            if (function_exists("mb_strpos")) {
                return mb_strpos($haystack, $needle) !== false;
            }

            return strpos($haystack, $needle) !== false;
        }

        throw new Exception("Invalid haystack");
    }

    /**
     * Length filter. If an array/object is passed a count is performed otherwise a strlen/mb_strlen
     */
    public function length($item) : int
    {
        if (is_object($item) || is_array($item)) {
            return count($item);
        }

        if (function_exists("mb_strlen")) {
            return mb_strlen($item);
        }

        return strlen($item);
    }

    /**
     * Renders a view using the template engine
     */
    public function render(string $templatePath, $params, bool $mustClean = false)
    {
        if ($mustClean) {
            ob_clean();
        }

        /**
         * The compilation process is done by Phiz\Mvc\View\Engine\Volt\Compiler
         */
        $compiler = $this->getCompiler();
        $eventsManager = $this->eventsManager;

        if (is_object($eventsManager)) {
            if ($eventsManager->fire("view:beforeCompile", $this) === false) {
                return null;
            }
        }

        $compiler->compile($templatePath);

        if (is_object($eventsManager)) {
            if ($eventsManager->fire("view:afterCompile", $this) === false) {
                return null;
            }
        }

        $compiledTemplatePath = $compiler->getCompiledTemplatePath();

        /**
         * Export the variables the current symbol table
         */
        if (is_array($params)) {
            extract($params);
        }

        require $compiledTemplatePath;

        if ($mustClean) {
            $this->view->setContent(ob_get_contents());
        }
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager) : void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Set Volt's options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Extracts a $slice from a string/array/traversable object value
     */
    public function slice($value, int $start = 0, $end = null)
    {
        /**
         * Objects must implement a Traversable interface
         */
        if (is_object($value)) {
            if ($end === null) {
                $end = count($value) - 1;
            }

            $position = 0;
            $slice = [];

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
        if ($end !== null) {
            $length = ($end - $start) + 1;
        } else {
            $length = null;
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
        if (function_exists("mb_substr")) {
            if ($length !== null) {
                return mb_substr($value, $start, $length);
            }

            return mb_substr($value, $start);
        }

        /**
         * Use the standard substr function
         */
        if ($length !== null) {
            return substr($value, $start, $length);
        }

        return substr($value, $start);
    }

    /**
     * Sorts an array
     */
    public function sort(array $value) : array
    {
        asort($value);

        return $value;
    }
}
