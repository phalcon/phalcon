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

namespace Phalcon\Support;

use ErrorException;
use Phalcon\Support\Debug\Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Throwable;

use function get_included_files;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function mb_strtolower;
use function memory_get_usage;
use function sprintf;

/**
 * @property array  $blacklist
 * @property mixed  $data
 * @property bool   $hideDocumentRoot
 * @property bool   $isActive
 * @property bool   $showBackTrace
 * @property bool   $showFileFragment
 * @property bool   $showFiles
 * @property string $uri
 */
class Debug
{
    /**
     * @var array
     */
    protected array $blacklist = ["request" => [], "server" => []];

    /**
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * @var bool
     */
    protected bool $hideDocumentRoot = false;

    /**
     * @var bool
     */
    protected static bool $isActive = false;

    /**
     * @var bool
     */
    protected bool $showBackTrace = true;

    /**
     * @var bool
     */
    protected bool $showFileFragment = false;

    /**
     * @var bool
     */
    protected bool $showFiles = true;

    /**
     * @var string
     */
    protected string $uri = "https://assets.phalcon.io/debug/6.0.x/";

    /**
     * Clears are variables added previously
     *
     * @return $this
     */
    public function clearVars(): Debug
    {
        $this->data = null;

        return $this;
    }

    /**
     * Adds a variable to the debug output
     *
     * @param mixed $variable
     *
     * @return $this
     */
    public function debugVar(mixed $variable): Debug
    {
        $this->data[] = [
            $variable,
            debug_backtrace(),
            time(),
        ];

        return $this;
    }

    /**
     * Returns the CSS sources
     *
     * @return string
     */
    public function getCssSources(): string
    {
        $template = "
    <link href='" . $this->uri . "%s'
          rel='stylesheet' 
          type='text/css' />";

        return sprintf($template, 'assets/jquery-ui/themes/ui-lightness/jquery-ui.min.css')
            . sprintf($template, 'assets/jquery-ui/themes/ui-lightness/theme.css')
            . sprintf($template, 'themes/default/style.css');
    }

    /**
     * Returns the JavaScript sources
     *
     * @return string
     */
    public function getJsSources(): string
    {
        $template = "
    <script type='application/javascript' 
            src='" . $this->uri . "%s'></script>";

        return sprintf($template, 'assets/jquery/dist/jquery.min.js')
            . sprintf($template, 'assets/jquery-ui/jquery-ui.min.js')
            . sprintf($template, 'assets/jquery.scrollTo/jquery.scrollTo.min.js')
            . sprintf($template, 'prettify/prettify.js')
            . sprintf($template, 'pretty.js');
    }

    /**
     * Generates a link to the current version documentation
     */
    public function getVersion(): string
    {
        $version = new Version();
        $link    = "https://docs.phalcon.io/"
            . $version->getPart(Version::VERSION_MAJOR)
            . "."
            . $version->getPart(Version::VERSION_MEDIUM)
            . "/";

        return "<div class='version'>
    Phalcon Framework <a href='$link' target='_new'>" . $version->get() . "</a>
</div>";
    }

    /**
     * Halts the request showing a backtrace
     *
     * @throws Exception
     */
    public function halt(): void
    {
        throw new Exception('Halted request');
    }

    /**
     * Listen for uncaught exceptions and non silent notices or warnings
     *
     * @param bool $exceptions
     * @param bool $lowSeverity
     *
     * @return Debug
     */
    public function listen(
        bool $exceptions = true,
        bool $lowSeverity = false
    ): Debug {
        if (true === $exceptions) {
            $this->listenExceptions();
        }

        if (true === $lowSeverity) {
            $this->listenLowSeverity();
        }

        return $this;
    }

    /**
     * Listen for uncaught exceptions
     *
     * @return Debug
     */
    public function listenExceptions(): Debug
    {
        set_exception_handler([$this, 'onUncaughtException']);

        return $this;
    }

    /**
     * Listen for non silent notices or warnings
     *
     * @return Debug
     */
    public function listenLowSeverity(): Debug
    {
        set_error_handler([$this, 'onUncaughtLowSeverity']);
        set_exception_handler([$this, 'onUncaughtException']);

        return $this;
    }

    /**
     * Handles uncaught exceptions
     *
     * @param Throwable $exception
     *
     * @return bool
     * @throws ReflectionException
     */
    public function onUncaughtException(Throwable $exception): bool
    {
        $obLevel = ob_get_level();

        /**
         * Cancel the output buffer if active
         */
        if ($obLevel > 0) {
            ob_end_clean();
        }

        /**
         * Avoid that multiple exceptions being showed
         */
        if (true !== self::$isActive) {
            /**
             * Globally block the debug component to avoid other exceptions to be shown
             */
            self::$isActive = true;

            /**
             * Print the HTML, @TODO, add an option to store the HTML
             */
            echo $this->renderHtml($exception);

            /**
             * Unlock the exception renderer
             */
            self::$isActive = false;

            return true;
        }

        echo $exception->getMessage();

        return false;
    }

    /**
     * Throws an exception when a notice or warning is raised
     *
     * @param int    $severity
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws ErrorException
     */
    public function onUncaughtLowSeverity(
        int $severity,
        string $message,
        string $file,
        int $line
    ): void {
        if (error_reporting() & $severity) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    }

    /**
     * Render exception to html format.
     *
     * @param Throwable $exception
     *
     * @return string
     * @throws ReflectionException
     */
    public function renderHtml(Throwable $exception): string
    {
        $className = get_class($exception);

        /**
         * Escape the exception's message avoiding possible XSS injections?
         */
        $escapedMessage = $this->escapeString($exception->getMessage());

        /**
         * CSS static sources to style the error presentation
         * Use the exception info as document's title
         */

        $html = "<!DOCTYPE html>
<html lang='en'>
<head>
    <title>$className:$escapedMessage</title>" . $this->getCssSources() . "
</head>
<body>
";

        /**
         * Get the version link
         */
        $html .= $this->getVersion();

        /**
         * Main exception info
         */
        $html .= "
<div align='center'>
    <div class='error-main'>
        <h1>$className: $escapedMessage</h1>
        <span class='error-file'>" . $exception->getFile() . " (" . $exception->getLine() . ")</span>
    </div>";

        /**
         * Check if the developer wants to show the backtrace or not
         */
        if (true === $this->showBackTrace) {
            /**
             * Create the tabs in the page
             */
            $html .= "
            
    <div class='error-info'>
        <div id='tabs'>
            <ul>
                <li><a href='#backtrace'>Backtrace</a></li>
                <li><a href='#request'>Request</a></li>
                <li><a href='#server'>Server</a></li>
                <li><a href='#files'>Included Files</a></li>
                <li><a href='#memory'>Memory</a></li>";

            if (is_array($this->data)) {
                $html .= "
                <li><a href='#variables'>Variables</a></li>";
            }

            $html .= "
            </ul>";

            /**
             * Print backtrace
             */
            $html .= $this->printBacktrace($exception);

            /**
             * Print _REQUEST superglobal
             */
            $html .= $this->printSuperglobal($_REQUEST, 'request');
            /**
             * Print _SERVER superglobal
             */
            $html .= $this->printSuperglobal($_SERVER, 'server');

            /**
             * Show included files
             */
            $html .= $this->printIncludedFiles();

            /**
             * Memory usage
             */
            $html .= $this->printMemoryUsage();

            /**
             * Print extra variables passed to the component
             */
            $html .= $this->printExtraVariables();

            $html .= "
            </div>";
        }

        /**
         * Get JavaScript sources
         */
        return $html . $this->getJsSources() . "
        </div>
    </body>
</html>";
    }

    /**
     * Sets if files the exception's backtrace must be showed
     *
     * @param array $blacklist
     *
     * @return $this
     */
    public function setBlacklist(array $blacklist): Debug
    {
        $area     = $blacklist['request'] ?? [];
        $subArray = [];
        $result   = [];

        foreach ($area as $value) {
            $subArray[mb_strtolower($value)] = 1;
        }

        $result['request'] = $subArray;
        $area              = $blacklist['server'] ?? [];
        $subArray          = [];

        foreach ($area as $value) {
            $subArray[mb_strtolower($value)] = 1;
        }

        $result['server'] = $subArray;
        $this->blacklist  = $result;

        return $this;
    }

    /**
     * Sets if files the exception's backtrace must be showed
     *
     * @param bool $showBackTrace
     *
     * @return $this
     */
    public function setShowBackTrace(bool $showBackTrace): Debug
    {
        $this->showBackTrace = $showBackTrace;

        return $this;
    }

    /**
     * Sets if files must be completely opened and showed in the output
     * or just the fragment related to the exception
     *
     * @param bool $showFileFragment
     *
     * @return Debug
     */
    public function setShowFileFragment(bool $showFileFragment): Debug
    {
        $this->showFileFragment = $showFileFragment;

        return $this;
    }

    /**
     * Set if files part of the backtrace must be shown in the output
     *
     * @param bool $showFiles
     *
     * @return Debug
     */
    public function setShowFiles(bool $showFiles): Debug
    {
        $this->showFiles = $showFiles;

        return $this;
    }

    /**
     * Change the base URI for static resources
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri(string $uri): Debug
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Escapes a string with htmlentities
     *
     * @param string $value
     *
     * @return string
     */
    protected function escapeString(string $value): string
    {
        return htmlentities(
            str_replace("\n", "\\n", $value),
            ENT_COMPAT,
            'utf-8'
        );
    }

    /**
     * Produces a recursive representation of an array
     *
     * @param array $arguments
     * @param int   $number
     *
     * @return string|null
     */
    protected function getArrayDump(array $arguments, int $number = 0): string | null
    {
        if ($number >= 3 || empty($arguments)) {
            return null;
        }

        if (count($arguments) >= 10) {
            return (string)count($arguments);
        }

        $dump = [];
        foreach ($arguments as $index => $argument) {
            if ('' === $argument) {
                $varDump = '(empty string)';
            } elseif (is_scalar($argument)) {
                $varDump = $this->escapeString((string)$argument);
            } elseif (is_array($argument)) {
                $varDump = 'Array(' . $this->getArrayDump($argument, $number + 1) . ')';
            } elseif (is_object($argument)) {
                $varDump = 'Object(' . get_class($argument) . ')';
            } elseif (null === $argument) {
                $varDump = 'null';
            } else {
                $varDump = $argument;
            }

            $dump[] = '[' . $index . '] =&gt; ' . $varDump;
        }

        return implode(', ', $dump);
    }

    /**
     * Produces a string representation of a variable
     *
     * @param mixed $variable
     *
     * @return string
     */
    protected function getVarDump(mixed $variable): string
    {
        if (true === $variable) {
            return 'true';
        }

        if (false === $variable) {
            return 'false';
        }

        /**
         * String variables are escaped to avoid XSS injections
         */
        if (is_string($variable)) {
            return $this->escapeString($variable);
        }

        /**
         * Scalar variables are just converted to strings
         */
        if (is_scalar($variable)) {
            return (string)$variable;
        }

        /**
         * If the variable is an object print its class name
         */
        if (is_object($variable)) {
            $className = get_class($variable);

            /**
             * Try to check for a 'dump' method, this surely produces a better
             * printable representation
             */
            if (true === method_exists($variable, 'dump')) {
                $dumpedObject = $variable->dump();

                /**
                 * dump() must return an array, generate a recursive
                 * representation using `getArrayDump()`
                 */
                return 'Object(' . $className . ': ' . $this->getArrayDump($dumpedObject) . ')';
            }

            /**
             * If dump() is not available just print the class name
             */
            return 'Object(' . $className . ')';
        }

        /**
         * Recursively process the array and enclose it in []
         */
        if (is_array($variable)) {
            return 'Array(' . $this->getArrayDump($variable) . ')';
        }

        /**
         * Null variables are represented as 'null'
         */
        if (null === $variable) {
            return 'null';
        }

        /**
         * Other types are represented by its type
         */
        return gettype($variable);
    }

    /**
     * Shows a backtrace item
     *
     * @param int   $number
     * @param array $trace
     *
     * @return string
     * @throws ReflectionException
     */
    final protected function showTraceItem(int $number, array $trace): string
    {
        /**
         * Every trace in the backtrace have a unique number
         */
        $html = "
                    <tr>
                        <td style='text-align: right; vertical-align: top'
                            class='error-number'>
                            #
                        </td>
                        <td>";

        if (isset($trace['class'])) {
            $className = $trace['class'];
            /**
             * We assume that classes starting by Phalcon are framework's
             * classes
             */
            if (str_starts_with($className, "Phalcon")) {
                /**
                 * Prepare the class name according to the Phalcon's conventions
                 */
                $parts           = explode("\\", $className);
                $prepareUriClass = $parts[0] . '_' . $parts[1];

                /**
                 * Generate a link to the official docs
                 */
                $classNameWithLink = "<a target='_new' "
                    . "href='https://docs.phalcon.io/6.0/en/api/$prepareUriClass'>"
                    . "$className</a>";
            } else {
                $classReflection = new ReflectionClass($className);

                /**
                 * Check if classes are PHP's classes
                 */
                if (true === $classReflection->isInternal()) {
                    $prepareInternalClass = str_replace(
                        '_',
                        '-',
                        mb_strtolower($className)
                    );

                    /**
                     * Generate a link to the official docs
                     */
                    $classNameWithLink = "<a target='_new' "
                        . "href='https://secure.php.net/manual/en/class.$prepareInternalClass.php'>"
                        . "$className</a>";
                } else {
                    $classNameWithLink = $className;
                }
            }

            $html .= "
                        <span class='error-class'>
                            $classNameWithLink
                        </span>";

            /**
             * Object access operator: static/instance
             */
            $html .= $trace['type'];
        }

        /**
         * Normally the backtrace contains only classes
         */
        /** @var string $functionName */
        $functionName = $trace['function'];

        if (isset($trace['class'])) {
            $functionNameWithLink = $functionName;
        } else {
            /**
             * Check if the function exists
             */
            if (true === function_exists($functionName)) {
                $functionReflection = new ReflectionFunction($functionName);
                /**
                 * Internal functions links to the PHP documentation
                 */
                if (true === $functionReflection->isInternal()) {
                    /**
                     * Prepare function's name according to the conventions in the docs
                     */
                    /** @var string $preparedFunctionName */
                    $preparedFunctionName = str_replace(
                        '_',
                        '-',
                        $functionName
                    );

                    $functionNameWithLink = "<a target='_new' "
                        . "href='https://secure.php.net/manual/en/function.$preparedFunctionName.php'>"
                        . "$functionName</a>";
                } else {
                    $functionNameWithLink = $functionName;
                }
            } else {
                $functionNameWithLink = $functionName;
            }
        }

        $html .= "
                        <span class='error-function'>
                            $functionNameWithLink
                        </span>";

        /**
         * Check for arguments in the function
         */
        if (isset($trace['args'])) {
            $arguments = [];
            foreach ($trace['args'] as $argument) {
                /**
                 * Every argument is generated using getVarDump
                 * Append the HTML generated to the argument's list
                 */
                $arguments[] = "
                        <span class='error-parameter'>
                            " . $this->getVarDump($argument) . "
                        </span>";
            }

            /**
             * Join all the arguments
             */
            $html .= '(' . implode(', ', $arguments) . ')';
        }

        /**
         * When 'file' is present, it usually means the function is provided by
         * the user
         */
        if (isset($trace['file'])) {
            $file = $trace['file'];
            $line = $trace['line'];

            /**
             * Realpath to the file and its line using a special header
             */
            $html .= "
                        <br/>
                        <div class='error-file'>
                            $file ($line)
                        </div>";

            /**
             * The developer can change if the files must be opened or not
             */
            if (true === $this->showFiles) {
                /**
                 * Open the file to an array using 'file', this respects the
                 * openbase-dir directive
                 */
                $lines       = file($file);
                $numberLines = count($lines);

                /**
                 * File fragments just show a piece of the file where the
                 * exception is located
                 */
                if (true === $this->showFileFragment) {
                    /**
                     * Take seven lines back to the current exception's line, @TODO add an option for this
                     */
                    $beforeLine = $line - 7;

                    /**
                     * Check for overflows
                     */
                    $firstLine = ($beforeLine < 1) ? 1 : $beforeLine;

                    /**
                     * Take five lines after the current exception's line, @TODO add an option for this
                     */
                    $afterLine = $line + 5;

                    /**
                     * Check for overflows
                     */
                    $lastLine = ($afterLine > $numberLines) ? $numberLines : $afterLine;


                    $html .= " 
                        <pre class='prettyprint highlight:$firstLine:$line linenums:$firstLine . '>";
                } else {
                    $firstLine = 1;
                    $lastLine  = $numberLines;
                    $html      .= "
                        <pre class='prettyprint highlight:$firstLine:$line linenums error-scroll'>";
                }

                $counter = $firstLine;

                while ($counter <= $lastLine) {
                    /**
                     * Current line in the file
                     */
                    $linePosition = $counter - 1;

                    /**
                     * Current line content in the piece of file
                     */
                    $currentLine = $lines[$linePosition];

                    /**
                     * File fragments are cleaned, removing tabs and comments
                     */
                    if (
                        true === $this->showFileFragment &&
                        $counter === $firstLine &&
                        preg_match("#\\*\\/#", rtrim($currentLine))
                    ) {
                        $currentLine = str_replace(
                            '* /',
                            ' ',
                            $currentLine
                        );
                    }

                    /**
                     * Print a non break space if the current line is a line
                     * break, this allows to show the HTML zebra properly
                     */
                    if ("\n" === $currentLine || "\r\n" === $currentLine) {
                        $html .= "&nbsp;\n";
                    } else {
                        /**
                         * Don't escape quotes
                         * We assume the file is utf-8 encoded, @TODO add an option for this
                         */
                        $html .= htmlentities(
                            str_replace("\t", '  ', $currentLine),
                            ENT_COMPAT,
                            'UTF-8'
                        );
                    }

                    $counter++;
                }

                $html .= "
                            </pre>";
            }
        }

        return $html . "
                    </td>
                </tr>";
    }

    /**
     * @return string
     */
    private function closeTable(): string
    {
        return "
                </tbody>
            </table>
        </div>";
    }

    /**
     * @param Throwable $exception
     *
     * @return string
     * @throws ReflectionException
     */
    private function printBacktrace(Throwable $exception): string
    {
        $html = "
        
        <div id='backtrace'>
            <table style='border-collapse: collapse; border-spacing: 0; text-align=center; width:100%'>
                <tbody>";

        $trace = $exception->getTrace();
        foreach ($trace as $number => $item) {
            /**
             * Every line in the trace is rendered using 'showTraceItem'
             */
            $html .= $this->showTraceItem($number, $item);
        }

        return $html . $this->closeTable();
    }

    /**
     * @return string
     */
    private function printExtraVariables(): string
    {
        $html = '';
        if (is_array($this->data)) {
            $html .= $this->printTableHeader('variables', 'Key', 'Value');

            foreach ($this->data as $key => $value) {
                $html .= '<tr><td class="key">'
                    . $key . '</td><td>'
                    . $this->getVarDump($value[0])
                    . '</td></tr>';
            }

            $html .= $this->closeTable();
        }

        return $html;
    }

    /**
     * @return string
     */
    private function printIncludedFiles(): string
    {
        $html = $this->printTableHeader('files', '#', 'Path');

        $files = get_included_files();
        foreach ($files as $key => $value) {
            $html .= "
                        <tr>
                            <td>$key</td>
                            <td>$value</td>
                        </tr>";
        }

        return $html . $this->closeTable();
    }

    /**
     * @return string
     */
    private function printMemoryUsage(): string
    {
        return $this->printTableHeader('memory', 'Memory', '')
            . "
                    <tr>
                        <td>
                            Usage
                        </td>
                        <td>"
            . memory_get_usage(true)
            . "</td>
                    </tr>
                </tbody>
                </table>
            </div>";
    }

    /**
     * @param array $source
     *
     * @return string
     */
    private function printSuperglobal(array $source, string $div): string
    {
        /**
         * Print $_REQUEST or $_SERVER superglobal
         */
        $html   = $this->printTableHeader($div, 'Key', 'Value');
        $filter = $this->blacklist['server'] ?? [];

        foreach ($source as $key => $value) {
            if (!isset($filter[mb_strtolower($key)])) {
                $html .= "
                    <tr>
                        <td class='key'>$key</td>
                        <td>" . $this->getVarDump($value) . "</td>
                    </tr>";
            }
        }

        return $html . "
                    </tbody>
                </table>
            </div>";
    }

    /**
     * @param string $divId
     * @param string $headerOne
     * @param string $headerTwo
     * @param string $colspan
     *
     * @return string
     */
    private function printTableHeader(
        string $divId,
        string $headerOne,
        string $headerTwo,
        string $colspan = ""
    ): string {
        $span = (empty($colspan)) ? "" : ' colspan="' . $colspan . '"';

        return "
        <div id='$divId'>
            <table style='border-collapse: collapse; border-spacing: 0; text-align: center' 
                   class='superglobal-detail'>
                <thead>
                <tr>
                    <th$span>$headerOne</th>
                </tr>
                <tr>
                    <th>$headerTwo</th>
                </tr>
                </thead>
                <tbody>";
    }
}
