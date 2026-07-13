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

require_once dirname(__DIR__) . '/vendor/autoload.php';

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

/**
 * Merges every `--coverage-php` report produced by the unit and database
 * test legs into a single Clover XML report, so SonarQube and octocov both
 * read the exact same combined coverage figure instead of computing it
 * independently (SonarQube via its own multi-path glob merge, octocov via
 * whatever it was pointed at) - a single merged file is the source of truth.
 *
 * Usage: php bin/merge-coverage.php <input-dir> <output-file>
 *   <input-dir>   directory searched recursively for *.cov files (default: cv)
 *   <output-file> merged Clover XML output path (default: tests/_output/coverage.xml)
 */

$inputDir   = $argv[1] ?? 'cv';
$outputFile = $argv[2] ?? 'tests/_output/coverage.xml';

$files = glob(rtrim($inputDir, '/') . '/**/*.cov');

if ($files === false || $files === []) {
    fwrite(STDERR, "No *.cov files found under {$inputDir}" . PHP_EOL);
    exit(1);
}

$merged = null;

foreach ($files as $file) {
    /** @var CodeCoverage $coverage */
    $coverage = include $file;

    if ($merged === null) {
        $merged = $coverage;
    } else {
        $merged->merge($coverage);
    }
}

(new Clover())->process($merged, $outputFile);

$report = $merged->getReport();

echo 'Merged ' . count($files) . ' coverage report(s)' . PHP_EOL;
echo 'Covered statements: ' . $report->numberOfExecutedLines() . '/' . $report->numberOfExecutableLines() . PHP_EOL;
echo 'Line coverage: ' . number_format($report->percentageOfExecutedLines()->asFloat(), 2) . '%' . PHP_EOL;
