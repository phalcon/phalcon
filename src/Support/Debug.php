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
use Phalcon\Contracts\Support\SupportTypes;
use Phalcon\Support\Debug\Exceptions\RequestHalted;
use Phalcon\Support\Debug\Exceptions\RuntimeWarning;
use Phalcon\Support\Debug\Renderer\HtmlRenderer;
use Phalcon\Support\Debug\ReportBuilder;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;
use ReflectionException;
use Throwable;

use function mb_strtolower;

/**
 * Listens for uncaught exceptions and renders them. Acts as a thin coordinator
 * delegating data collection to ReportBuilder and presentation to a Renderer.
 *
 * @phpstan-import-type support_debug_blacklist from SupportTypes
 * @phpstan-import-type support_debug_blacklist_input from SupportTypes
 * @phpstan-import-type support_debug_variables from SupportTypes
 */
class Debug
{
    use GetTrait;

    protected static bool $isActive = false;
    /**
     * @phpstan-var support_debug_blacklist
     */
    protected array $blacklist = ["request" => [], "server" => []];
    /**
     * @phpstan-var support_debug_variables
     */
    protected array $data = [];
    protected bool $hideDocumentRoot = false;
    protected Renderer $renderer;
    protected ReportBuilder $reportBuilder;
    protected bool $showBackTrace = true;
    protected bool $showFileFragment = false;
    protected bool $showFiles = true;
    protected string $uri = "https://assets.phalcon.io/debug/6.0.x/";

    public function __construct()
    {
        $this->renderer      = new HtmlRenderer();
        $this->reportBuilder = new ReportBuilder();
    }

    /**
     * Clears are variables added previously
     */
    public function clearVars(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * Adds a variable to the debug output
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
     */
    public function getCssSources(): string
    {
        return $this->renderer->getCssSources($this->uri);
    }

    /**
     * Returns the JavaScript sources
     */
    public function getJsSources(): string
    {
        return $this->renderer->getJsSources($this->uri);
    }

    /**
     * Returns the renderer used to produce the output
     */
    public function getRenderer(): Renderer
    {
        return $this->renderer;
    }

    /**
     * Generates a link to the current version documentation
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
     */
    public function listenExceptions(): static
    {
        /**
         * PHP ignores whatever the handler returns, so the `bool` this one
         * gives back is compatible with the `void` the signature asks for.
         *
         * @var callable(Throwable): void $exceptionHandler
         */
        $exceptionHandler = [$this, 'onUncaughtException'];

        set_exception_handler($exceptionHandler);

        return $this;
    }

    /**
     * Listen for non silent notices or warnings
     */
    public function listenLowSeverity(): static
    {
        /**
         * Returning nothing tells PHP to fall through to the standard error
         * handler, which is exactly what this one does when the severity is
         * masked out.
         *
         * @var callable(int, string, string, int): bool $errorHandler
         */
        $errorHandler = [$this, 'onUncaughtLowSeverity'];
        /** @var callable(Throwable): void $exceptionHandler */
        $exceptionHandler = [$this, 'onUncaughtException'];

        set_error_handler($errorHandler);
        set_exception_handler($exceptionHandler);

        return $this;
    }

    /**
     * Handles uncaught exceptions
     *
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
     * @phpstan-param support_debug_blacklist_input $blacklist
     */
    public function setBlacklist(array $blacklist): static
    {
        /** @var array<array-key, string> $area */
        $area     = $this->getArrVal($blacklist, 'request', []);
        $subArray = [];
        $result   = [];

        foreach ($area as $value) {
            $subArray[mb_strtolower($value)] = 1;
        }

        $result['request'] = $subArray;
        /** @var array<array-key, string> $area */
        $area              = $this->getArrVal($blacklist, 'server', []);
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
     */
    public function setRenderer(Renderer $renderer): static
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Sets if files the exception's backtrace must be showed
     */
    public function setShowBackTrace(bool $showBackTrace): static
    {
        $this->showBackTrace = $showBackTrace;

        return $this;
    }

    /**
     * Sets if files must be completely opened and showed in the output
     * or just the fragment related to the exception
     */
    public function setShowFileFragment(bool $showFileFragment): static
    {
        $this->showFileFragment = $showFileFragment;

        return $this;
    }

    /**
     * Set if files part of the backtrace must be shown in the output
     */
    public function setShowFiles(bool $showFiles): static
    {
        $this->showFiles = $showFiles;

        return $this;
    }

    /**
     * Change the base URI for static resources
     */
    public function setUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }
}
