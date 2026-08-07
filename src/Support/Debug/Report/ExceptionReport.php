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
    protected array $backtrace = [];
    protected array $includedFiles = [];
    protected int $memoryUsage = 0;
    protected int $peakMemoryUsage = 0;
    protected array $request = [];
    protected array $server = [];
    protected array $variables = [];

    public function __construct(
        protected readonly string $className,
        protected readonly string $message,
        protected readonly string $file,
        protected readonly int $line,
        protected readonly bool $showBackTrace,
        protected readonly string $uri,
    ) {
    }

    /**
     * @return BacktraceItem[]
     */
    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getIncludedFiles(): array
    {
        return $this->includedFiles;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getMemoryUsage(): int
    {
        return $this->memoryUsage;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPeakMemoryUsage(): int
    {
        return $this->peakMemoryUsage;
    }

    public function getRequest(): array
    {
        return $this->request;
    }

    public function getServer(): array
    {
        return $this->server;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function hasVariables(): bool
    {
        return !empty($this->variables);
    }

    public function isShowBackTrace(): bool
    {
        return $this->showBackTrace;
    }

    /**
     * @param BacktraceItem[] $backtrace
     */
    public function setBacktrace(array $backtrace): static
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    public function setIncludedFiles(array $includedFiles): static
    {
        $this->includedFiles = $includedFiles;

        return $this;
    }

    public function setMemoryUsage(int $memoryUsage): static
    {
        $this->memoryUsage = $memoryUsage;

        return $this;
    }

    public function setPeakMemoryUsage(int $peakMemoryUsage): static
    {
        $this->peakMemoryUsage = $peakMemoryUsage;

        return $this;
    }

    public function setRequest(array $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function setServer(array $server): static
    {
        $this->server = $server;

        return $this;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }
}
