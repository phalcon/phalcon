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

use Phalcon\Contracts\Support\Debug\Renderer;
use Phalcon\Support\Debug\Exceptions\RequestHalted;
use Phalcon\Support\Debug\Exceptions\RuntimeWarning;
use Phalcon\Support\Debug\Renderer\HtmlRenderer;
use Phalcon\Support\Debug\ReportBuilder;
use ReflectionException;
use Throwable;

use function mb_strtolower;

/**
 * Listens for uncaught exceptions and renders them. Acts as a thin coordinator
 * delegating data collection to ReportBuilder and presentation to a Renderer.
 *
 * @property array         $blacklist
 * @property array         $data
 * @property bool          $hideDocumentRoot
 * @property bool          $isActive
 * @property Renderer      $renderer
 * @property ReportBuilder $reportBuilder
 * @property bool          $showBackTrace
 * @property bool          $showFileFragment
 * @property bool          $showFiles
 * @property string        $uri
 */
class Debug
{
    /**
     * @var array
     */
    protected array $blacklist = ["request" => [], "server" => []];

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var bool
     */
    protected bool $hideDocumentRoot = false;

    /**
     * @var bool
     */
    protected static bool $isActive = false;

    /**
     * @var Renderer
     */
    protected Renderer $renderer;

    /**
     * @var ReportBuilder
     */
    protected ReportBuilder $reportBuilder;

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

    public function __construct()
    {
        $this->renderer      = new HtmlRenderer();
        $this->reportBuilder = new ReportBuilder();
    }

    /**
     * Clears are variables added previously
     *
     * @return $this
     */
    public function clearVars(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * Adds a variable to the debug output
     *
     * @param mixed $variable
     *
     * @return $this
     */
    public function debugVar(mixed $variable): static
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
        return $this->renderer->getCssSources($this->uri);
    }

    /**
     * Returns the JavaScript sources
     *
     * @return string
     */
    public function getJsSources(): string
    {
        return $this->renderer->getJsSources($this->uri);
    }

    /**
     * Returns the renderer used to produce the output
     *
     * @return Renderer
     */
    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    /**
     * Generates a link to the current version documentation
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->renderer->getVersion();
    }

    /**
     * Halts the request showing a backtrace
     *
     * @throws RequestHalted
     */
    public function halt(): void
    {
        throw new RequestHalted();
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
    ): static {
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
    public function listenExceptions(): static
    {
        set_exception_handler([$this, 'onUncaughtException']);

        return $this;
    }

    /**
     * Listen for non silent notices or warnings
     *
     * @return Debug
     */
    public function listenLowSeverity(): static
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
     * @throws RuntimeWarning
     */
    public function onUncaughtLowSeverity(
        int $severity,
        string $message,
        string $file,
        int $line
    ): void {
        if (error_reporting() & $severity) {
            throw new RuntimeWarning($message, 0, $severity, $file, $line);
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
        return $this->renderer->render(
            $this->reportBuilder->build(
                $exception,
                $this->blacklist,
                $this->showBackTrace,
                $this->showFiles,
                $this->showFileFragment,
                $this->uri,
                $this->data
            )
        );
    }

    /**
     * Sets if files the exception's backtrace must be showed
     *
     * @param array $blacklist
     *
     * @return $this
     */
    public function setBlacklist(array $blacklist): static
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
     * Sets the renderer used to produce the output
     *
     * @param Renderer $renderer
     *
     * @return $this
     */
    public function setRenderer(Renderer $renderer): static
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Sets if files the exception's backtrace must be showed
     *
     * @param bool $showBackTrace
     *
     * @return $this
     */
    public function setShowBackTrace(bool $showBackTrace): static
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
    public function setShowFileFragment(bool $showFileFragment): static
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
    public function setShowFiles(bool $showFiles): static
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
    public function setUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }
}
