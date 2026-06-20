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

namespace Phalcon\Support\Debug\Report;

/**
 * Carries all data collected for an exception, ready to be rendered. Holds no
 * presentation logic.
 */
final class ExceptionReport
{
    /**
     * @var BacktraceItem[]
     */
    private array $backtrace = [];

    /**
     * @var array
     */
    private array $includedFiles = [];

    /**
     * @var int
     */
    private int $memoryUsage = 0;

    /**
     * @var int
     */
    private int $peakMemoryUsage = 0;

    /**
     * @var array
     */
    private array $request = [];

    /**
     * @var array
     */
    private array $server = [];

    /**
     * @var array
     */
    private array $variables = [];

    /**
     * @param string $className
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param bool   $showBackTrace
     * @param string $uri
     */
    public function __construct(
        private readonly string $className,
        private readonly string $message,
        private readonly string $file,
        private readonly int $line,
        private readonly bool $showBackTrace,
        private readonly string $uri,
    ) {
    }

    /**
     * @return BacktraceItem[]
     */
    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getIncludedFiles(): array
    {
        return $this->includedFiles;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getMemoryUsage(): int
    {
        return $this->memoryUsage;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getPeakMemoryUsage(): int
    {
        return $this->peakMemoryUsage;
    }

    /**
     * @return array
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getServer(): array
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @return bool
     */
    public function hasVariables(): bool
    {
        return !empty($this->variables);
    }

    /**
     * @return bool
     */
    public function isShowBackTrace(): bool
    {
        return $this->showBackTrace;
    }

    /**
     * @param BacktraceItem[] $backtrace
     *
     * @return static
     */
    public function setBacktrace(array $backtrace): static
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    /**
     * @param array $includedFiles
     *
     * @return static
     */
    public function setIncludedFiles(array $includedFiles): static
    {
        $this->includedFiles = $includedFiles;

        return $this;
    }

    /**
     * @param int $memoryUsage
     *
     * @return static
     */
    public function setMemoryUsage(int $memoryUsage): static
    {
        $this->memoryUsage = $memoryUsage;

        return $this;
    }

    /**
     * @param int $peakMemoryUsage
     *
     * @return static
     */
    public function setPeakMemoryUsage(int $peakMemoryUsage): static
    {
        $this->peakMemoryUsage = $peakMemoryUsage;

        return $this;
    }

    /**
     * @param array $request
     *
     * @return static
     */
    public function setRequest(array $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param array $server
     *
     * @return static
     */
    public function setServer(array $server): static
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @param array $variables
     *
     * @return static
     */
    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }
}
