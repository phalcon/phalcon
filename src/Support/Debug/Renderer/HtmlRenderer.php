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

namespace Phalcon\Support\Debug\Renderer;

use Phalcon\Contracts\Support\Debug\Renderer;
use Phalcon\Support\Debug\Report\BacktraceItem;
use Phalcon\Support\Debug\Report\ExceptionReport;
use Phalcon\Support\Debug\Traits\TemplateAwareTrait;
use Phalcon\Support\Version;
use Phalcon\Traits\Support\Helper\Str\InterpolateTrait;

use function count;
use function get_class;
use function gettype;
use function htmlentities;
use function implode;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function number_format;
use function rtrim;
use function str_replace;
use function strpos;

use const ENT_COMPAT;
use const PHP_VERSION;

/**
 * Renders an ExceptionReport as the HTML debug page using embedded, overridable
 * template strings filled by the interpolator. All styling and interactivity
 * (theme, tabs, syntax highlighting, copy/editor links) are provided by the
 * external debug.css / debug.js assets.
 */
class HtmlRenderer implements Renderer
{
    use InterpolateTrait;
    use TemplateAwareTrait;

    /**
     * @param string $uri
     *
     * @return string
     */
    public function getCssSources(string $uri): string
    {
        return $this->toInterpolate(
            $this->getTemplate('cssLink'),
            ['uri' => $uri, 'path' => 'debug.css']
        );
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function getJsSources(string $uri): string
    {
        return $this->toInterpolate(
            $this->getTemplate('jsLink'),
            ['uri' => $uri, 'path' => 'debug.js']
        );
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $version = new Version();
        $link    = "https://docs.phalcon.io/"
            . $version->getPart(Version::VERSION_MAJOR)
            . "."
            . $version->getPart(Version::VERSION_MEDIUM)
            . "/";

        return $this->toInterpolate(
            $this->getTemplate('version'),
            [
                'link'    => $link,
                'version' => $version->get(),
            ]
        );
    }

    /**
     * @param ExceptionReport $report
     *
     * @return string
     */
    public function render(ExceptionReport $report): string
    {
        $className      = $report->getClassName();
        $escapedMessage = $this->escapeString($report->getMessage());

        $html = $this->toInterpolate(
            $this->getTemplate('document'),
            [
                'className'      => $className,
                'escapedMessage' => $escapedMessage,
                'cssSources'     => $this->getCssSources($report->getUri()),
            ]
        );

        $html .= $this->toInterpolate(
            $this->getTemplate('masthead'),
            ['version' => $this->getVersion()]
        );

        $html .= $this->toInterpolate(
            $this->getTemplate('errorMain'),
            [
                'className'      => $className,
                'escapedMessage' => $escapedMessage,
                'file'           => $report->getFile(),
                'line'           => (string)$report->getLine(),
                'phpVersion'     => PHP_VERSION,
            ]
        );

        if (true === $report->isShowBackTrace()) {
            $html .= $this->renderTabs($report);
            $html .= $this->renderBacktrace($report->getBacktrace());
            $html .= $this->renderSuperglobal('request', $report->getRequest());
            $html .= $this->renderSuperglobal('server', $report->getServer());
            $html .= $this->renderIncludedFiles($report->getIncludedFiles());
            $html .= $this->renderMemory($report);
            $html .= $this->renderVariables($report->getVariables());
        }

        return $html
            . $this->getTemplate('wrapClose')
            . $this->getJsSources($report->getUri())
            . $this->getTemplate('documentClose');
    }

    /**
     * Returns the embedded default template for the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function defaultTemplate(string $name): string
    {
        return match ($name) {
            'cssLink'        => "
    <link href='%uri%%path%'
          rel='stylesheet'
          type='text/css' />",
            'jsLink'         => "
    <script src='%uri%%path%'></script>",
            'version'        => "<a class='version-badge' href='%link%' target='_new'><b>v%version%</b></a>",
            'document'       => "<!DOCTYPE html>
<html lang='en' data-theme='light'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>%className%:%escapedMessage%</title>%cssSources%
</head>
<body>
<div class='wrap'>",
            'masthead'       => "
    <div class='masthead'>
        <div class='brand'><img class='logo'"
                . " src='https://assets.phalcon.io/phalcon/images/svg/logo--tablet.svg'"
                . " alt='Phalcon' /><span>Phalcon Debug</span></div>
        <div class='actions-top'>
            <button class='btn' data-action='copy-trace'>Copy trace</button>
            <button class='btn' data-action='toggle-theme' title='Toggle theme'>Theme</button>
            %version%
        </div>
    </div>",
            'errorMain'      => "
    <div class='error-card'>
        <span class='error-type'>%className%</span>
        <h1 class='error-message'>%escapedMessage%</h1>
        <div class='meta'>
            <span class='item'><code>%file%</code> : <code>%line%</code></span>
            <span class='sep'>|</span><span class='item'>PHP <code>%phpVersion%</code></span>
        </div>
    </div>",
            'tabs'           => "
    <div class='tabs' role='tablist'>
        <button class='tab is-active' data-tab='backtrace'>Backtrace "
                . "<span class='count'>%backtraceCount%</span></button>
        <button class='tab' data-tab='request'>Request <span class='count'>%requestCount%</span></button>
        <button class='tab' data-tab='server'>Server <span class='count'>%serverCount%</span></button>
        <button class='tab' data-tab='files'>Included Files <span class='count'>%filesCount%</span></button>
        <button class='tab' data-tab='memory'>Memory</button>%variablesTab%
    </div>",
            'variablesTab'   => "
        <button class='tab' data-tab='variables'>Variables <span class='count'>%variablesCount%</span></button>",
            'backtracePanel' => "
    <div class='panel is-active' id='backtrace'>
        <div class='bt-tools'>
            <button class='btn' data-action='expand-all'>Expand all</button>
            <button class='btn' data-action='collapse-all'>Collapse all</button>
        </div>",
            'panelOpen'      => "
    <div class='panel' id='%id%'>",
            'panelClose'     => "
    </div>",
            'wrapClose'      => "
</div>",
            'documentClose'  => "
</body>
</html>",
            'frameOpen'      => "
        <details class='frame %appClass%'%open%>
            <summary><div class='frame-head'>
                <span class='frame-num'>#%num%</span>
                <span class='frame-call'>%signature%</span>%appTag%
                <span class='chev'>&#9656;</span>
            </div></summary>",
            'appTag'         => "<span class='tag-app'>app</span>",
            'frameFile'      => "
            <div class='frame-file' data-file='%file%' data-line='%line%'>
                <span class='path'><b>%file%</b> : %line%</span>
            </div>",
            'frameClose'     => "
        </details>",
            'codeOpen'       => "
            <div class='code'><table>",
            'codeRow'        => "<tr%hlClass%><td class='ln'>%num%</td><td class='src'>%src%</td></tr>",
            'codeClose'      => "</table></div>",
            'link'           => "<a href='%url%' target='_new'>%name%</a>",
            'tableOpen'      => "<table class='grid'><thead><tr><th>%headerOne%</th><th>%headerTwo%</th></tr>"
                . "</thead><tbody>",
            'gridRow'        => "<tr><td class='k'>%key%</td><td class='v'>%value%</td></tr>",
            'tableClose'     => "</tbody></table>",
            'memory'         => "
        <div class='stats'>
            <div class='stat'><div class='label'>Memory usage (real)</div>"
                . "<div class='value'>%memory% <small>MB</small></div></div>
            <div class='stat'><div class='label'>Peak usage</div>"
                . "<div class='value'>%peak% <small>MB</small></div></div>
        </div>",
            default          => '',
        };
    }

    /**
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

        if (is_string($variable)) {
            return $this->escapeString($variable);
        }

        if (is_scalar($variable)) {
            return (string)$variable;
        }

        if (is_object($variable)) {
            $className = get_class($variable);

            if (true === method_exists($variable, 'dump')) {
                $dumpedObject = $variable->dump();

                return 'Object(' . $className . ': ' . $this->getArrayDump($dumpedObject) . ')';
            }

            return 'Object(' . $className . ')';
        }

        if (is_array($variable)) {
            return 'Array(' . $this->getArrayDump($variable) . ')';
        }

        if (null === $variable) {
            return 'null';
        }

        return gettype($variable);
    }

    /**
     * @param int $bytes
     *
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        return number_format($bytes / 1048576, 1);
    }

    /**
     * Frames whose file lives outside a vendor directory are application code.
     *
     * @param string|null $file
     *
     * @return bool
     */
    private function isApp(string | null $file): bool
    {
        return null !== $file && false === strpos($file, '/vendor/');
    }

    /**
     * @param BacktraceItem[] $backtrace
     *
     * @return string
     */
    private function renderBacktrace(array $backtrace): string
    {
        $html = $this->getTemplate('backtracePanel');
        foreach ($backtrace as $index => $item) {
            $html .= $this->renderTraceItem($index, $item);
        }

        return $html . $this->getTemplate('panelClose');
    }

    /**
     * @param array $fragment
     *
     * @return string
     */
    private function renderFragment(array $fragment): string
    {
        $firstLine = $fragment['firstLine'];
        $lastLine  = $fragment['lastLine'];
        $line      = $fragment['line'];
        $lines     = $fragment['lines'];

        $html    = $this->getTemplate('codeOpen');
        $counter = $firstLine;
        while ($counter <= $lastLine) {
            $currentLine = rtrim($lines[$counter - 1] ?? '', "\r\n");

            $html .= $this->toInterpolate(
                $this->getTemplate('codeRow'),
                [
                    'hlClass' => ($counter === $line) ? " class='hl'" : '',
                    'num'     => (string)$counter,
                    'src'     => htmlentities(
                        str_replace("\t", '  ', $currentLine),
                        ENT_COMPAT,
                        'UTF-8'
                    ),
                ]
            );

            $counter++;
        }

        return $html . $this->getTemplate('codeClose');
    }

    /**
     * @param array $files
     *
     * @return string
     */
    private function renderIncludedFiles(array $files): string
    {
        $html = $this->toInterpolate($this->getTemplate('panelOpen'), ['id' => 'files'])
            . $this->toInterpolate(
                $this->getTemplate('tableOpen'),
                ['headerOne' => '#', 'headerTwo' => 'Path']
            );

        foreach ($files as $key => $value) {
            $html .= $this->toInterpolate(
                $this->getTemplate('gridRow'),
                [
                    'key'   => (string)$key,
                    'value' => $this->escapeString((string)$value),
                ]
            );
        }

        return $html . $this->getTemplate('tableClose') . $this->getTemplate('panelClose');
    }

    /**
     * @param ExceptionReport $report
     *
     * @return string
     */
    private function renderMemory(ExceptionReport $report): string
    {
        return $this->toInterpolate($this->getTemplate('panelOpen'), ['id' => 'memory'])
            . $this->toInterpolate(
                $this->getTemplate('memory'),
                [
                    'memory' => $this->formatBytes($report->getMemoryUsage()),
                    'peak'   => $this->formatBytes($report->getPeakMemoryUsage()),
                ]
            )
            . $this->getTemplate('panelClose');
    }

    /**
     * @param BacktraceItem $item
     *
     * @return string
     */
    private function renderSignature(BacktraceItem $item): string
    {
        $html = '';

        if (null !== $item->getClassName()) {
            $name = $this->escapeString($item->getClassName());
            $link = $item->getClassLink();
            $classHtml = (null !== $link)
                ? $this->toInterpolate($this->getTemplate('link'), ['url' => $link, 'name' => $name])
                : $name;

            $html .= "<span class='cls'>" . $classHtml . "</span>";
            $html .= "<span class='op'>" . (string)$item->getType() . "</span>";
        }

        $fnName = $this->escapeString($item->getFunctionName());
        $fnLink = $item->getFunctionLink();
        $functionHtml = (null !== $fnLink)
            ? $this->toInterpolate($this->getTemplate('link'), ['url' => $fnLink, 'name' => $fnName])
            : $fnName;

        $html .= "<span class='fn'>" . $functionHtml . "</span>";

        if (true === $item->hasArgs()) {
            $arguments = [];
            foreach ($item->getArgs() as $argument) {
                $arguments[] = $this->getVarDump($argument);
            }

            $html .= "<span class='op'>(</span>" . implode(', ', $arguments) . "<span class='op'>)</span>";
        }

        return $html;
    }

    /**
     * @param string $div
     * @param array  $source
     *
     * @return string
     */
    private function renderSuperglobal(string $div, array $source): string
    {
        $html = $this->toInterpolate($this->getTemplate('panelOpen'), ['id' => $div])
            . $this->toInterpolate(
                $this->getTemplate('tableOpen'),
                ['headerOne' => 'Key', 'headerTwo' => 'Value']
            );

        foreach ($source as $key => $value) {
            $html .= $this->toInterpolate(
                $this->getTemplate('gridRow'),
                [
                    'key'   => $this->escapeString((string)$key),
                    'value' => $this->getVarDump($value),
                ]
            );
        }

        return $html . $this->getTemplate('tableClose') . $this->getTemplate('panelClose');
    }

    /**
     * @param ExceptionReport $report
     *
     * @return string
     */
    private function renderTabs(ExceptionReport $report): string
    {
        $variablesTab = '';
        if (true === $report->hasVariables()) {
            $variablesTab = $this->toInterpolate(
                $this->getTemplate('variablesTab'),
                ['variablesCount' => (string)count($report->getVariables())]
            );
        }

        return $this->toInterpolate(
            $this->getTemplate('tabs'),
            [
                'backtraceCount' => (string)count($report->getBacktrace()),
                'requestCount'   => (string)count($report->getRequest()),
                'serverCount'    => (string)count($report->getServer()),
                'filesCount'     => (string)count($report->getIncludedFiles()),
                'variablesTab'   => $variablesTab,
            ]
        );
    }

    /**
     * @param int           $index
     * @param BacktraceItem $item
     *
     * @return string
     */
    private function renderTraceItem(int $index, BacktraceItem $item): string
    {
        $isApp = $this->isApp($item->getFile());

        $html = $this->toInterpolate(
            $this->getTemplate('frameOpen'),
            [
                'appClass'  => $isApp ? 'app' : 'vendor',
                'open'      => (0 === $index) ? ' open' : '',
                'num'       => (string)$index,
                'signature' => $this->renderSignature($item),
                'appTag'    => $isApp ? $this->getTemplate('appTag') : '',
            ]
        );

        if (null !== $item->getFile()) {
            $html .= $this->toInterpolate(
                $this->getTemplate('frameFile'),
                [
                    'file' => $item->getFile(),
                    'line' => (string)$item->getLine(),
                ]
            );

            $fragment = $item->getFragment();
            if (null !== $fragment) {
                $html .= $this->renderFragment($fragment);
            }
        }

        return $html . $this->getTemplate('frameClose');
    }

    /**
     * @param array $variables
     *
     * @return string
     */
    private function renderVariables(array $variables): string
    {
        if (empty($variables)) {
            return '';
        }

        $html = $this->toInterpolate($this->getTemplate('panelOpen'), ['id' => 'variables'])
            . $this->toInterpolate(
                $this->getTemplate('tableOpen'),
                ['headerOne' => 'Key', 'headerTwo' => 'Value']
            );

        foreach ($variables as $key => $value) {
            $html .= $this->toInterpolate(
                $this->getTemplate('gridRow'),
                [
                    'key'   => $this->escapeString((string)$key),
                    'value' => $this->getVarDump($value[0]),
                ]
            );
        }

        return $html . $this->getTemplate('tableClose') . $this->getTemplate('panelClose');
    }
}
