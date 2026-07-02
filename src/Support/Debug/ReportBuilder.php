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

namespace Phalcon\Support\Debug;

use Phalcon\Support\Debug\Report\BacktraceItem;
use Phalcon\Support\Debug\Report\ExceptionReport;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Throwable;

use function count;
use function explode;
use function file;
use function function_exists;
use function get_class;
use function get_included_files;
use function mb_strtolower;
use function memory_get_peak_usage;
use function memory_get_usage;
use function str_replace;
use function str_starts_with;

/**
 * Collects the runtime data for an exception (backtrace, superglobals, included
 * files, memory, variables) into an ExceptionReport. Holds no presentation
 * logic.
 */
class ReportBuilder
{
    /**
     * @param Throwable $exception
     * @param array     $blacklist
     * @param bool      $showBackTrace
     * @param bool      $showFiles
     * @param bool      $showFileFragment
     * @param string    $uri
     * @param array     $data
     *
     * @return ExceptionReport
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
        $report = new ExceptionReport(
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $showBackTrace,
            $uri
        );

        if (true !== $showBackTrace) {
            return $report;
        }

        $items = [];
        foreach ($exception->getTrace() as $trace) {
            $items[] = $this->buildItem($trace, $showFiles, $showFileFragment);
        }

        $report
            ->setBacktrace($items)
            ->setRequest($this->filter($_REQUEST, $blacklist['request'] ?? []))
            ->setServer($this->filter($_SERVER, $blacklist['server'] ?? []))
            ->setIncludedFiles(get_included_files())
            ->setMemoryUsage(memory_get_usage(true))
            ->setPeakMemoryUsage(memory_get_peak_usage(true))
            ->setVariables($data);

        return $report;
    }

    /**
     * @param string $file
     * @param int    $line
     * @param bool   $showFileFragment
     *
     * @return array
     */
    private function buildFragment(string $file, int $line, bool $showFileFragment): array
    {
        $lines = file($file);
        if (false === $lines) {
            $lines = [];
        }

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
     * @param array $trace
     * @param bool  $showFiles
     * @param bool  $showFileFragment
     *
     * @return BacktraceItem
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
        if (isset($trace['file'])) {
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
     * @param array $source
     * @param array $blacklist
     *
     * @return array
     */
    private function filter(array $source, array $blacklist): array
    {
        $result = [];
        foreach ($source as $key => $value) {
            if (!isset($blacklist[mb_strtolower((string) $key)])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $className
     *
     * @return string|null
     * @throws ReflectionException
     */
    private function resolveClassLink(string $className): string | null
    {
        if (str_starts_with($className, 'Phalcon')) {
            $parts = explode('\\', $className);

            return 'https://docs.phalcon.io/6.0/en/api/' . $parts[0] . '_' . $parts[1];
        }

        $reflection = new ReflectionClass($className);
        if (true === $reflection->isInternal()) {
            $prepared = str_replace('_', '-', mb_strtolower($className));

            return 'https://secure.php.net/manual/en/class.' . $prepared . '.php';
        }

        return null;
    }

    /**
     * @param string $functionName
     *
     * @return string|null
     * @throws ReflectionException
     */
    private function resolveFunctionLink(string $functionName): string | null
    {
        if (true !== function_exists($functionName)) {
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
