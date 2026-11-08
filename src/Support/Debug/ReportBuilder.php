<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Debug;

use Phalcon\Contracts\Support\SupportTypes;
use Phalcon\Support\Debug\Report\BacktraceItem;
use Phalcon\Support\Debug\Report\ExceptionReport;
use Phalcon\Support\Version;
use Phalcon\Traits\Php\InfoTrait;
use Phalcon\Traits\Support\Helper\Arr\GetTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Throwable;

use function count;
use function explode;
use function file;
use function get_class;
use function get_included_files;
use function is_file;
use function is_readable;
use function mb_strtolower;
use function memory_get_peak_usage;
use function memory_get_usage;
use function str_replace;
use function str_starts_with;

/**
 * Collects the runtime data for an exception (backtrace, superglobals, included
 * files, memory, variables) into an ExceptionReport. Holds no presentation
 * logic.
 *
 * @phpstan-import-type support_debug_blacklist from SupportTypes
 * @phpstan-import-type support_debug_fragment from SupportTypes
 * @phpstan-import-type support_debug_superglobal from SupportTypes
 * @phpstan-import-type support_debug_trace from SupportTypes
 * @phpstan-import-type support_debug_variables from SupportTypes
 */
class ReportBuilder
{
    use GetTrait;
    use InfoTrait;

    /**
     * The lines of each file that the current report read
     *
     * @var array<string, array<int, string>>
     */
    private array $fileLines = [];

    /**
     * @phpstan-param support_debug_blacklist $blacklist
     * @phpstan-param support_debug_variables $data
     *
     * @throws ReflectionException
     */
    public function build(
        Throwable $exception,
        array $blacklist,
        bool $showBackTrace,
        bool $showFiles,
        bool $showFileFragment,
        string $uri,
        array $data
    ): ExceptionReport {
        $this->fileLines = [];

        $report = new ExceptionReport(
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $showBackTrace,
            $uri
        );

        $previousExceptions = [];
        $previous           = $exception->getPrevious();
        while (null !== $previous) {
            $previousExceptions[] = [
                'className' => get_class($previous),
                'message'   => $previous->getMessage(),
                'file'      => $previous->getFile(),
                'line'      => $previous->getLine(),
            ];
            $previous = $previous->getPrevious();
        }

        $report->setPreviousExceptions($previousExceptions);

        if (true !== $showBackTrace) {
            return $report;
        }

        $items = [];
        foreach ($exception->getTrace() as $trace) {
            $items[] = $this->buildItem($trace, $showFiles, $showFileFragment);
        }

        /** @var array<string, int> $requestBlacklist */
        $requestBlacklist = $this->getArrVal($blacklist, 'request', []);
        /** @var array<string, int> $serverBlacklist */
        $serverBlacklist = $this->getArrVal($blacklist, 'server', []);

        $report
            ->setBacktrace($items)
            ->setRequest($this->filter($_REQUEST, $requestBlacklist))
            ->setServer($this->filter($_SERVER, $serverBlacklist))
            ->setIncludedFiles(get_included_files())
            ->setMemoryUsage(memory_get_usage(true))
            ->setPeakMemoryUsage(memory_get_peak_usage(true))
            ->setVariables($data);

        return $report;
    }

    /**
     * @phpstan-return support_debug_fragment
     */
    private function buildFragment(string $file, int $line, bool $showFileFragment): array
    {
        $lines       = $this->readLines($file);
        $numberLines = count($lines);

        if (true === $showFileFragment) {
            $beforeLine = $line - 7;
            $firstLine  = ($beforeLine < 1) ? 1 : $beforeLine;
            $afterLine  = $line + 5;
            $lastLine   = ($afterLine > $numberLines) ? $numberLines : $afterLine;
            $mode       = 'fragment';
        } else {
            $firstLine = 1;
            $lastLine  = $numberLines;
            $mode      = 'full';
        }

        return [
            'mode'      => $mode,
            'firstLine' => $firstLine,
            'line'      => $line,
            'lastLine'  => $lastLine,
            'lines'     => $lines,
        ];
    }

    /**
     * @phpstan-param support_debug_trace $trace
     *
     * @param array<array-key, mixed> $trace
     *
     * @throws ReflectionException
     */
    private function buildItem(array $trace, bool $showFiles, bool $showFileFragment): BacktraceItem
    {
        $className    = null;
        $classLink    = null;
        $type         = null;
        $functionLink = null;

        if (isset($trace['class'])) {
            $className = $trace['class'];
            $type      = $trace['type'] ?? null;
            $classLink = $this->resolveClassLink($className);
        }

        $functionName = $trace['function'];
        if (!isset($trace['class'])) {
            $functionLink = $this->resolveFunctionLink($functionName);
        }

        $hasArgs = isset($trace['args']);
        $args    = $trace['args'] ?? [];

        $file     = null;
        $line     = null;
        $fragment = null;
        if (isset($trace['file'], $trace['line'])) {
            $file = $trace['file'];
            $line = $trace['line'];

            if (true === $showFiles) {
                $fragment = $this->buildFragment($file, $line, $showFileFragment);
            }
        }

        return new BacktraceItem(
            $functionName,
            $type,
            $className,
            $classLink,
            $functionLink,
            $hasArgs,
            $args,
            $file,
            $line,
            $fragment
        );
    }

    /**
     * @phpstan-param  support_debug_superglobal $source
     * @phpstan-param  array<string, int>        $blacklist
     * @phpstan-return support_debug_superglobal
     */
    private function filter(array $source, array $blacklist): array
    {
        $result = [];
        foreach ($source as $key => $value) {
            if (!isset($blacklist[mb_strtolower((string) $key)])) {
                if (is_array($value)) {
                    $value = $this->filter($value, $blacklist);
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Reads each file one time for each report. It reads the file only if it
     * exists and is readable. This prevents a warning for eval()'d code.
     *
     * @return array<int, string>
     */
    private function readLines(string $file): array
    {
        if (!isset($this->fileLines[$file])) {
            $lines = false;
            if (is_file($file) && is_readable($file)) {
                $lines = file($file);
            }

            $this->fileLines[$file] = (false === $lines) ? [] : $lines;
        }

        return $this->fileLines[$file];
    }

    /**
     * @phpstan-param class-string $className
     *
     * @throws ReflectionException
     */
    private function resolveClassLink(string $className): string | null
    {
        if (str_starts_with($className, 'Phalcon\\')) {
            $parts   = explode('\\', $className);
            $version = new Version();

            return 'https://docs.phalcon.io/'
                . $version->getPart(Version::VERSION_MAJOR) . '.'
                . $version->getPart(Version::VERSION_MEDIUM)
                . '/api/' . mb_strtolower($parts[0] . '_' . $parts[1]) . '/';
        }

        $reflection = new ReflectionClass($className);
        if (true === $reflection->isInternal()) {
            $prepared = str_replace('_', '-', mb_strtolower($className));

            return 'https://secure.php.net/manual/en/class.' . $prepared . '.php';
        }

        return null;
    }

    /**
     * @throws ReflectionException
     */
    private function resolveFunctionLink(string $functionName): string | null
    {
        if (true !== $this->phpFunctionExists($functionName)) {
            return null;
        }

        $reflection = new ReflectionFunction($functionName);
        if (true !== $reflection->isInternal()) {
            return null;
        }

        $prepared = str_replace('_', '-', $functionName);

        return 'https://secure.php.net/manual/en/function.' . $prepared . '.php';
    }
}
