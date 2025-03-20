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

namespace Phalcon\Mvc\View\Engine\Volt;

use Closure;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Mvc\View\ViewBaseInterface;
use Phalcon\Parsers\Volt\Enum;
use Phalcon\Support\Traits\FilePathTrait;
use Phalcon\Traits\Helper\Str\CamelizeTrait;
use Phalcon\Volt\Exception;
use Phalcon\Volt\Parser\Parser;

use function addslashes;
use function array_key_exists;
use function array_unshift;
use function call_user_func;
use function call_user_func_array;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function hash;
use function implode;
use function is_array;
use function is_bool;
use function is_object;
use function is_string;
use function lcfirst;
use function method_exists;
use function preg_replace;
use function realpath;
use function serialize;
use function str_replace;
use function strlen;
use function unserialize;

/**
 * This class reads and compiles Volt templates into PHP plain code
 *
 *```php
 * $compiler = new \Phalcon\Mvc\View\Engine\Volt\Compiler();
 *
 * $compiler->compile("views/partials/header.volt");
 *
 * require $compiler->getCompiledTemplatePath();
 *```
 */
class Compiler implements InjectionAwareInterface
{
    use CamelizeTrait;
    use FilePathTrait;
    use InjectionAwareTrait;

    /**
     * @var bool
     */
    protected bool $autoescape = false;

    /**
     * @var int
     */
    protected int $blockLevel = 0;

    /**
     * @var array|null
     *
     * TODO: Make array only?
     */
    protected array | null $blocks = null;

    /**
     * @var string|null
     */
    protected string | null $compiledTemplatePath;

    /**
     * @var string|null
     */
    protected string | null $currentBlock = null;

    /**
     * @var string|null
     */
    protected string | null $currentPath = null;

    /**
     * @var int
     */
    protected int $exprLevel = 0;

    /**
     * @var bool
     */
    protected bool $extended = false;
    /**
     * @var array|bool
     *
     * TODO: Make it always array
     */
    protected array | bool $extendedBlocks;
    /**
     * @var array
     */
    protected array $extensions = [];
    /**
     * @var array
     */
    protected array $filters = [];
    /**
     * @var array
     */
    protected array $forElsePointers = [];
    /**
     * @var int
     */
    protected int $foreachLevel = 0;
    /**
     * @var array
     */
    protected array $functions = [];

    /**
     * @var int
     */
    protected int $level = 0;

    /**
     * @var array
     */
    protected array $loopPointers = [];

    /**
     * @var array
     */
    protected array $macros = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var string
     */
    protected string $prefix = "";

    /**
     * Phalcon\Mvc\View\Engine\Volt\Compiler
     *
     * @param ViewBaseInterface|null $view
     */
    public function __construct(
        protected ViewBaseInterface | null $view = null
    ) {
    }

    /**
     * Registers a Volt's extension
     *
     * @param mixed $extension
     *
     * @return $this
     * @throws Exception
     */
    public function addExtension(mixed $extension): Compiler
    {
        if (!is_object($extension)) {
            throw new Exception("The extension is not valid");
        }

        /**
         * Initialize the extension
         */
        if (method_exists($extension, "initialize")) {
            $extension->initialize($this);
        }

        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Register a new filter in the compiler
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return $this
     */
    public function addFilter(string $name, mixed $definition): Compiler
    {
        $this->filters[$name] = $definition;

        return $this;
    }

    /**
     * Register a new function in the compiler
     *
     * @param string $name
     * @param mixed  $definition
     *
     * @return $this
     */
    public function addFunction(string $name, mixed $definition): Compiler
    {
        $this->functions[$name] = $definition;

        return $this;
    }

    /**
     * Resolves attribute reading
     *
     * @param array $expr
     *
     * @return string
     * @throws Exception
     */
    public function attributeReader(array $expr): string
    {
        $exprCode = "";

        $left = $expr["left"];

        if ($left["type"] == Enum::PHVOLT_T_IDENTIFIER) {
            $variable = $left["value"];

            /**
             * Check if the variable is the loop context
             */
            if ($variable === "loop") {
                $level                      = $this->foreachLevel;
                $exprCode                   .= "$" . $this->getUniquePrefix() . $level . "loop";
                $this->loopPointers[$level] = $level;
            } else {
                /**
                 * Services registered in the dependency injector container are
                 * available always
                 */
                if (null !== $this->container && $this->container->has($variable)) {
                    $exprCode .= '$this->' . $variable;
                } else {
                    $exprCode .= '$' . $variable;
                }
            }
        } else {
            $leftCode = $this->expression($left);
//            $leftType = $left["type"];
//
//            /**
//             * @todo What?
//             */
//            if (
//                $leftType != Enum::PHVOLT_T_DOT &&
//                $leftType != Enum::PHVOLT_T_FCALL
//            ) {
//                $exprCode .= $leftCode;
//            } else {
//                $exprCode .= $leftCode;
//            }
            $exprCode .= $leftCode;
        }

        $exprCode .= "->";
        $right    = $expr["right"];

        if ($right["type"] == Enum::PHVOLT_T_IDENTIFIER) {
            $exprCode .= $right["value"];
        } else {
            $exprCode .= $this->expression($right);
        }

        return $exprCode;
    }

    /**
     * Compiles a template into a file applying the compiler options
     * This method does not return the compiled path if the template was not compiled
     *
     *```php
     * $compiler->compile("views/layouts/main.volt");
     *
     * require $compiler->getCompiledTemplatePath();
     *```
     *
     * @param string $templatePath
     * @param bool   $extendsMode
     *
     * @return array|mixed|string|null
     * @throws Exception
     */
    public function compile(string $templatePath, bool $extendsMode = false): mixed
    {
        /**
         * Re-initialize some properties already initialized when the object is
         * cloned
         */
        $this->extended       = false;
        $this->extendedBlocks = false;
        $this->blocks         = null;
        $this->level          = 0;
        $this->foreachLevel   = 0;
        $this->blockLevel     = 0;
        $this->exprLevel      = 0;

        $compilation = null;

        $options = $this->options;

        /**
         * This makes that templates will be compiled always
         */
        $compileAlways = $options["always"] ?? false;
        if (!is_bool($compileAlways)) {
            throw new Exception("'always' must be a bool value");
        }

        /**
         * Prefix is prepended to the template name
         */
        $prefix = $options["prefix"] ?? '';
        if (!is_string($prefix)) {
            throw new Exception("'prefix' must be a string");
        }

        /**
         * Compiled path is a directory where the compiled templates will be
         * located
         */
        $compiledPath = $options["path"] ?? '';

        /**
         * There is no compiled separator by default
         */
        $compiledSeparator = $options["separator"] ?? '%%';
        if (!is_string($compiledSeparator)) {
            throw new Exception("'separator' must be a string");
        }

        /**
         * By default the compile extension is .php
         */
        $compiledExtension = $options["extension"] ?? '.php';
        if (!is_string($compiledExtension)) {
            throw new Exception("'extension' must be a string");
        }

        /**
         * Stat option assumes the compilation of the file
         */
        $stat = $options["stat"] ?? true;

        /**
         * Check if there is a compiled path
         */
        if (is_string($compiledPath)) {
            /**
             * Calculate the template realpath's
             */
            if (!empty($compiledPath)) {
                /**
                 * Create the virtual path replacing the directory separator by
                 * the compiled separator
                 */
                $templateSepPath = $this->prepareVirtualPath(
                    realpath($templatePath),
                    $compiledSeparator
                );
            } else {
                $templateSepPath = $templatePath;
            }

            /**
             * In extends mode we add an additional 'e' suffix to the file
             */
            if ($extendsMode) {
                $compiledTemplatePath = $compiledPath
                    . $prefix
                    . $templateSepPath
                    . $compiledSeparator
                    . "e"
                    . $compiledSeparator
                    . $compiledExtension;
            } else {
                $compiledTemplatePath = $compiledPath
                    . $prefix
                    . $templateSepPath
                    . $compiledExtension;
            }
        } elseif ($compiledPath instanceof Closure) {
            /**
             * A closure can dynamically compile the path
             */
            $compiledTemplatePath = call_user_func_array(
                $compiledPath,
                [$templatePath, $options, $extendsMode]
            );

            /**
             * The closure must return a valid path
             */
            if (!is_string($compiledTemplatePath)) {
                throw new Exception(
                    "'path' closure didn't return a valid string"
                );
            }
        } else {
            throw new Exception(
                "'path' must be a string or a closure"
            );
        }

        /**
         * Compile always must be used only in the development stage
         */
        if (!file_exists($compiledTemplatePath) || $compileAlways) {
            /**
             * The file needs to be compiled because it either doesn't exist or
             * needs to compiled every time
             */
            $compilation = $this->compileFile(
                $templatePath,
                $compiledTemplatePath,
                $extendsMode
            );
        } else {
            if ($stat === true) {
                /**
                 * Compare modification timestamps to check if the file
                 * needs to be recompiled
                 */
                if (filemtime($templatePath) !== filemtime($compiledTemplatePath)) {
                    $compilation = $this->compileFile(
                        $templatePath,
                        $compiledTemplatePath,
                        $extendsMode
                    );
                } else {
                    if ($extendsMode) {
                        /**
                         * In extends mode we read the file that must
                         * contains a serialized array of blocks
                         */
                        $blocksCode = file_get_contents($compiledTemplatePath);

                        if ($blocksCode === false) {
                            throw new Exception(
                                "Extends compilation file "
                                . $compiledTemplatePath
                                . " could not be opened"
                            );
                        }

                        /**
                         * Unserialize the array blocks code
                         */
                        if ($blocksCode) {
                            $compilation = unserialize($blocksCode);
                        } else {
                            $compilation = [];
                        }
                    }
                }
            }
        }

        $this->compiledTemplatePath = $compiledTemplatePath;

        return $compilation;
    }

    /**
     * Compiles a "autoescape" statement returning PHP code
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileAutoEscape(array $statement, bool $extendsMode): string
    {
        /**
         * A valid option is required
         */
        if (!isset($statement["enable"])) {
            throw new Exception("Corrupted statement");
        }

        /**
         * "autoescape" mode
         */
        $autoescape       = (bool)$statement["enable"];
        $oldAutoescape    = $this->autoescape;
        $this->autoescape = $autoescape;

        $compilation = $this->statementList(
            $statement["block_statements"],
            $extendsMode
        );

        $this->autoescape = $oldAutoescape;

        return $compilation;
    }

    /**
     * Compiles calls to macros
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     */
    public function compileCall(array $statement, bool $extendsMode): string
    {
        // Not implemented?
        return '';
    }

    /**
     * Compiles a "case"/"default" clause returning PHP code
     *
     * @param array $statement
     * @param bool  $caseClause
     *
     * @return string
     * @throws Exception
     */
    public function compileCase(array $statement, bool $caseClause = true): string
    {
        if ($caseClause === false) {
            /**
             * "default" statement
             */
            return '<?php default: ?>';
        }

        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * "case" statement
         */
        return '<?php case ' . $this->expression($expr) . ': ?>';
    }

    /**
     * Compiles a "do" statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileDo(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * "Do" statement
         */
        return '<?php ' . $this->expression($expr) . '; ?>';
    }

    /**
     * Compiles a {% raw %}`{{` `}}`{% endraw %} statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileEcho(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * Evaluate common expressions
         */
        $exprCode = $this->expression($expr);

        if ($expr["type"] == Enum::PHVOLT_T_FCALL) {
            if ($this->isTagFactory($expr) === true) {
                $exprCode = $this->expression($expr, true);
            }

            $name = $expr["name"];
            /**
             * super() is a function however the return of this function
             * must be output as it is
             */
            if (
                $name["type"] == Enum::PHVOLT_T_IDENTIFIER &&
                $name["value"] == "super"
            ) {
                return $exprCode;
            }
        }

        /**
         * Echo statement
         */
        if ($this->autoescape) {
            return '<?= $this->escaper->html(' . $exprCode . ') ?>';
        }

        return '<?= ' . $exprCode . ' ?>';
    }

    /**
     * Compiles a "elseif" statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileElseIf(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * "elseif" statement
         */
        return '<?php } elseif (' . $this->expression($expr) . ') { ?>';
    }

    /**
     * Compiles a template into a file forcing the destination path
     *
     *```php
     * $compiler->compileFile(
     *     "views/layouts/main.volt",
     *     "views/layouts/main.volt.php"
     * );
     *```
     *
     * @param string $path
     * @param string $compiledPath
     * @param bool   $extendsMode
     *
     * @return array|string
     * @throws Exception
     */
    public function compileFile(
        string $path,
        string $compiledPath,
        bool $extendsMode = false
    ): array | string {
        if ($path == $compiledPath) {
            throw new Exception(
                "Template path and compilation template path cannot be the same"
            );
        }

        /**
         * Check if the template does exist
         */
        if (!file_exists($path)) {
            throw new Exception("Template file " . $path . " does not exist");
        }

        /**
         * Always use file_get_contents instead of read the file directly, this
         * respect the open_basedir directive
         */
        $viewCode = file_get_contents($path);

        if ($viewCode === false) {
            throw new Exception(
                "Template file " . $path . " could not be opened"
            );
        }

        $this->currentPath = $path;

        $compilation = $this->compileSource($viewCode, $extendsMode);

        /**
         * We store the file serialized if it's an array of blocks
         */
        $finalCompilation = $compilation;
        if (is_array($compilation)) {
            $finalCompilation = serialize($compilation);
        }

        /**
         * Always use file_put_contents to write files instead of write the file
         * directly, this respect the open_basedir directive
         */
        if (file_put_contents($compiledPath, $finalCompilation) === false) {
            throw new Exception("Volt directory can't be written");
        }

        return $compilation;
    }

    /**
     * Generates a 'forelse' PHP code
     *
     * @return string
     */
    public function compileForElse(): string
    {
        $level = $this->foreachLevel;

        if (!isset($this->forElsePointers[$level])) {
            return "";
        }

        $prefix = $this->forElsePointers[$level];
        if (isset($this->loopPointers[$level])) {
            return '<?php $' . $prefix . 'incr++; } if (!$' . $prefix . 'iterated) { ?>';
        }

        return '<?php } if (!$' . $prefix . 'iterated) { ?>';
    }

    /**
     * Compiles a "foreach" intermediate code representation into plain PHP code
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileForeach(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupted statement");
        }

        $this->foreachLevel++;
        $forElse     = null;
        $compilation = "";
        $prefix      = $this->getUniquePrefix();
        $level       = $this->foreachLevel;

        /**
         * prefixLevel is used to prefix every temporal variable
         */
        $prefixLevel = $prefix . $level;

        /**
         * Evaluate common expressions
         */
        $expr     = $statement["expr"];
        $exprCode = $this->expression($expr);

        /**
         * Process the block statements
         */
        $blockStatements = $statement["block_statements"];
        $forElse         = false;

        if (is_array($blockStatements)) {
            foreach ($blockStatements as $bstatement) {
                /**
                 * Check if the statement is valid
                 */
                if (!isset($bstatement["type"])) {
                    break;
                }

                $type = $bstatement["type"];

                if ($type == Enum::PHVOLT_T_ELSEFOR) {
                    $compilation                   .= '<?php $' . $prefixLevel . 'iterated = false; ?>';
                    $forElse                       = $prefixLevel;
                    $this->forElsePointers[$level] = $forElse;

                    break;
                }
            }
        }

        /**
         * Process statements block
         */
        $code        = $this->statementList($blockStatements, $extendsMode);
        $loopContext = $this->loopPointers;

        /**
         * Generate the loop context for the "foreach"
         */
        $iterator = $exprCode;
        if (isset($loopContext[$level])) {
            $compilation .= '<?php $' . $prefixLevel . 'iterator = ' . $exprCode . '; ';
            $compilation .= '$' . $prefixLevel . 'incr = 0; ';
            $compilation .= '$' . $prefixLevel . 'loop = new stdClass(); ';
            $compilation .= '$' . $prefixLevel . 'loop->self = &$' . $prefixLevel . 'loop; ';
            $compilation .= '$' . $prefixLevel . 'loop->length = count($' . $prefixLevel . 'iterator); ';
            $compilation .= '$' . $prefixLevel . 'loop->index = 1; ';
            $compilation .= '$' . $prefixLevel . 'loop->index0 = 1; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex = $' . $prefixLevel . 'loop->length; ';
            $compilation .= '$' . $prefixLevel . 'loop->revindex0 = $' . $prefixLevel . 'loop->length - 1; ?>';

            $iterator = '$' . $prefixLevel . 'iterator';
        }

        /**
         * Foreach statement
         */
        $variable = $statement["variable"];

        /**
         * Check if a "key" variable needs to be calculated
         */
        if (isset($statement["key"])) {
            $compilation .= '<?php foreach ('
                . $iterator
                . ' as $'
                . $statement["key"]
                . ' => $'
                . $variable
                . ") { ";
        } else {
            $compilation .= '<?php foreach ('
                . $iterator
                . ' as $'
                . $variable
                . ') { ';
        }

        /**
         * Check for an "if" expr in the block
         */
        if (isset($statement["if_expr"])) {
            $compilation .= 'if ('
                . $this->expression($statement["if_expr"])
                . ') { ?>';
        } else {
            $compilation .= '?>';
        }

        /**
         * Generate the loop context inside the cycle
         */
        if (isset($loopContext[$level])) {
            $compilation .= '<?php $'
                . $prefixLevel . 'loop->first = ($' . $prefixLevel . 'incr == 0); '
                . '$' . $prefixLevel . 'loop->index = $' . $prefixLevel . 'incr + 1; '
                . '$' . $prefixLevel . 'loop->index0 = $' . $prefixLevel . 'incr; '
                . '$' . $prefixLevel . 'loop->revindex = $' . $prefixLevel
                . 'loop->length - $' . $prefixLevel . 'incr; '
                . '$' . $prefixLevel . 'loop->revindex0 = $' . $prefixLevel
                . 'loop->length - ($' . $prefixLevel . 'incr + 1); '
                . '$' . $prefixLevel . 'loop->last = ($' . $prefixLevel
                . 'incr == ($' . $prefixLevel . 'loop->length - 1)); ?>';
        }

        /**
         * Update the forelse var if it's iterated at least one time
         */
        if (is_string($forElse)) {
            $compilation .= '<?php $' . $forElse . 'iterated = true; ?>';
        }

        /**
         * Append the internal block compilation
         */
        $compilation .= $code;

        if (isset($statement["if_expr"])) {
            $compilation .= '<?php } ?>';
        }

        if (is_string($forElse)) {
            $compilation .= '<?php } ?>';
        } else {
            if (isset($loopContext[$level])) {
                $compilation .= '<?php $' . $prefixLevel . 'incr++; } ?>';
            } else {
                $compilation .= '<?php } ?>';
            }
        }

        $this->foreachLevel--;

        return $compilation;
    }

    /**
     * Compiles a 'if' statement returning PHP code
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileIf(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * Process statements in the "true" block
         */
        $compilation = '<?php if ('
            . $this->expression($expr)
            . ') { ?>'
            . $this->statementList($statement["true_statements"], $extendsMode);

        /**
         * Check for a "else"/"elseif" block
         */
        if (isset($statement["false_statements"])) {
            /**
             * Process statements in the "false" block
             */
            $compilation .= '<?php } else { ?>'
                . $this->statementList($statement["false_statements"], $extendsMode);
        }

        $compilation .= '<?php } ?>';

        return $compilation;
    }

    /**
     * Compiles a 'include' statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileInclude(array $statement): string
    {
        /**
         * Include statement
         * A valid expression is required
         */
        if (!isset($statement["path"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $pathExpr = $statement["path"];

        /**
         * Check if the expression is a string
         * If the path is an string try to make an static compilation
         *
         * Static compilation cannot be performed if the user passed extra
         * parameters
         */
        if (
            $pathExpr["type"] == 260 &&
            !isset($statement["params"])
        ) {
            /**
             * Get the static path
             */
            $path      = $pathExpr["value"];
            $finalPath = $this->getFinalPath($path);

            /**
             * Clone the original compiler
             * Perform a sub-compilation of the included file
             * If the compilation doesn't return anything we include the compiled path
             */
            $subCompiler = clone $this;
            $compilation = $subCompiler->compile($finalPath);

            if ($compilation === null) {
                /**
                 * Use file-get-contents to respect the openbase_dir
                 * directive
                 */
                $compilation = file_get_contents(
                    $subCompiler->getCompiledTemplatePath()
                );
            }

            return $compilation;
        }

        /**
         * Resolve the path's expression
         */
        $path = $this->expression($pathExpr);

        /**
         * Use partial
         */
        if (!isset($statement["params"])) {
            return '<?php $this->partial(' . $path . '); ?>';
        }

        return '<?php $this->partial('
            . $path
            . ", "
            . $this->expression($statement["params"])
            . '); ?>';
    }

    /**
     * Compiles macros
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileMacro(array $statement, bool $extendsMode): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["name"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $name = $statement["name"];

        /**
         * Check if the macro is already defined
         */
        if (isset($this->macros[$name])) {
            throw new Exception("Macro '" . $name . "' is already defined");
        }

        /**
         * Register the macro
         */
        $this->macros[$name] = $name;
        $macroName           = '$' . "this->macros['" . $name . "']";
        $code                = "<?php ";

        if (!isset($statement["parameters"])) {
            $code .= $macroName . " = function() { ?>";
        } else {
            $parameters = $statement["parameters"];
            /**
             * Parameters are always received as an array
             */
            $code .= $macroName . ' = function($__p = null) { ';
            foreach ($parameters as $position => $parameter) {
                $variableName = $parameter["variable"];

                $code .= 'if (isset($__p[' . $position . '])) { ';
                $code .= '$' . $variableName . ' = $__p[' . $position . '];';
                $code .= ' } else { ';
                $code .= 'if (array_key_exists("' . $variableName . '", $__p)) { ';
                $code .= '$' . $variableName . ' = $__p["' . $variableName . '"];';
                $code .= ' } else { ';

                if (isset($parameter["default"])) {
                    $code .= '$'
                        . $variableName
                        . ' = '
                        . $this->expression($parameter["default"])
                        . ';';
                } else {
                    $code .= " throw new \\Phalcon\\Mvc\\View\\Exception(\"Macro '"
                        . $name
                        . "' was called without parameter: "
                        . $variableName . '"); ';
                }

                $code .= ' } } ';
            }

            $code .= ' ?>';
        }

        /**
         * Block statements are allowed
         */
        if (isset($statement["block_statements"])) {
            /**
             * Process statements block
             */
            $code .= $this->statementList($statement["block_statements"], $extendsMode)
                . '<?php }; ';
        } else {
            $code .= '<?php }; ';
        }

        /**
         * Bind the closure to the $this object allowing to call services
         */
        $code .= $macroName . ' = \Closure::bind(' . $macroName . ', $this); ?>';

        return $code;
    }

    /**
     * Compiles a "return" statement returning PHP code
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileReturn(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * "Return" statement
         */
        return '<?php return ' . $this->expression($expr) . '; ?>';
    }

    /**
     * Compiles a "set" statement returning PHP code. The method accepts an
     * array produced by the Volt parser and creates the `set` statement in PHP.
     * This method is not particularly useful in development, since it requires
     * advanced knowledge of the Volt parser.
     *
     * ```php
     * <?php
     *
     * use Phalcon\Mvc\View\Engine\Volt\Compiler;
     *
     * $compiler = new Compiler();
     *
     * // {% set a = ['first': 1] %}
     * $source = [
     *     "type" => 306,
     *     "assignments" => [
     *         [
     *             "variable" => [
     *                 "type" => 265,
     *                 "value" => "a",
     *                 "file" => "eval code",
     *                 "line" => 1
     *             ],
     *             "op" => 61,
     *             "expr" => [
     *                 "type" => 360,
     *                 "left" => [
     *                     [
     *                         "expr" => [
     *                             "type" => 258,
     *                             "value" => "1",
     *                             "file" => "eval code",
     *                             "line" => 1
     *                         ],
     *                         "name" => "first",
     *                         "file" => "eval code",
     *                         "line" => 1
     *                     ]
     *                 ],
     *                 "file" => "eval code",
     *                 "line" => 1
     *             ],
     *             "file" => "eval code",
     *             "line" => 1
     *         ]
     *     ]
     * ];
     *
     * echo $compiler->compileSet($source);
     * // <?php $a = ['first' => 1]; ?>";
     * ```
     *
     * @param array $statement
     *
     * @return string
     * @throws Exception
     */
    public function compileSet(array $statement): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["assignments"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $assignments = $statement["assignments"];
        $compilation = '<?php';

        /**
         * A single set can have several assignments
         */
        foreach ($assignments as $assignment) {
            $exprCode = $this->expression($assignment["expr"]);

            /**
             * Resolve the expression assigned
             */
            $target = $this->expression($assignment["variable"]);

            /**
             * Assignment operator
             * Generate the right operator
             */
            $operator = match ($assignment["op"]) {
                Enum::PHVOLT_T_ADD_ASSIGN => " += ",
                Enum::PHVOLT_T_SUB_ASSIGN => " -= ",
                Enum::PHVOLT_T_MUL_ASSIGN => " *= ",
                Enum::PHVOLT_T_DIV_ASSIGN => " /= ",
                default                   => " = ",
            };

            $compilation .= " " . $target . $operator . $exprCode . ";";
        }

        $compilation .= " ?>";

        return $compilation;
    }

    /**
     * Compiles a template into a string
     *
     *```php
     * echo $compiler->compileString({% raw %}'{{ "hello world" }}'{% endraw %});
     *```
     *
     * @param string $viewCode
     * @param bool   $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileString(string $viewCode, bool $extendsMode = false): string
    {
        $this->currentPath = "eval code";

        return $this->compileSource($viewCode, $extendsMode);
    }

    /**
     * Compiles a 'switch' statement returning PHP code
     *
     * @param array $statement
     * @param bool  $extendsMode
     *
     * @return string
     * @throws Exception
     */
    public function compileSwitch(array $statement, bool $extendsMode = false): string
    {
        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])) {
            throw new Exception("Corrupt statement", $statement);
        }

        $expr = $statement["expr"];

        /**
         * Process statements in the "true" block
         */
        $compilation = '<?php switch (' . $this->expression($expr) . '): ?>';

        /**
         * Check for a "case"/"default" blocks
         */
        if (isset($statement["case_clauses"])) {
            $caseClauses = $statement["case_clauses"];
            $lines       = $this->statementList($caseClauses, $extendsMode);

            /**
             * Any output (including whitespace) between a switch statement and
             * the first case will result in a syntax error. This is the
             * responsibility of the user. However, we can clear empty lines and
             * whitespace here to reduce the number of errors.
             *
             * https://php.net/control-structures.alternative-syntax
             */
            if (strlen($lines) !== 0) {
                /**
                 * (*ANYCRLF) - specifies a newline convention: (*CR), (*LF) or (*CRLF)
                 * \h+ - 1+ horizontal whitespace chars
                 * $ - end of line (now, before CR or LF)
                 * m - multiline mode on ($ matches at the end of a line).
                 * u - unicode
                 *
                 * g - global search, - is implicit with preg_replace(), you don't need to include it.
                 */
                $lines = preg_replace(
                    "/(*ANYCRLF)^\h+|\h+$|(\h){2,}/mu",
                    "",
                    $lines
                );
            }

            $compilation .= $lines;
        }

        $compilation .= "<?php endswitch ?>";

        return $compilation;
    }

    /**
     * Resolves an expression node in an AST volt tree
     *
     * @param array $expr
     * @param bool  $doubleQuotes
     *
     * @return string
     * @throws Exception
     */
    final public function expression(array $expr, bool $doubleQuotes = false): string
    {
        $exprCode  = null;
        $leftCode  = null;
        $rightCode = null;
        $this->exprLevel++;

        /**
         * Check if any of the registered extensions provide compilation for
         * this expression
         */
        while (true) {
            if (!empty($this->extensions)) {
                /**
                 * Notify the extensions about being resolving an expression
                 */
                $exprCode = $this->fireExtensionEvent(
                    "resolveExpression",
                    [$expr]
                );

                if (is_string($exprCode)) {
                    break;
                }
            }

            if (!isset($expr["type"])) {
                $items = [];

                foreach ($expr as $singleExpr) {
                    $singleExprCode = $this->expression(
                        $singleExpr["expr"],
                        $doubleQuotes
                    );

                    if (isset($singleExpr["name"])) {
                        $items[] = "'" . $singleExpr["name"] . "' => " . $singleExprCode;
                    } else {
                        $items[] = $singleExprCode;
                    }
                }

                $exprCode = implode(", ", $items);

                break;
            }

            $type = $expr["type"];

            /**
             * Attribute reading needs special handling
             */
            if ($type == Enum::PHVOLT_T_DOT) {
                $exprCode = $this->attributeReader($expr);

                break;
            }

            /**
             * Left part of expression is always resolved
             */
            if (isset($expr["left"])) {
                $leftCode = $this->expression($expr["left"], $doubleQuotes);
            }

            /**
             * Operator "is" also needs special handling
             */
            if ($type == Enum::PHVOLT_T_IS) {
                $exprCode = $this->resolveTest(
                    $expr["right"],
                    $leftCode
                );

                break;
            }

            /**
             * We don't resolve the right expression for filters
             */
            if ($type == Enum::PHVOLT_T_PIPE) {
                $exprCode = $this->resolveFilter(
                    $expr["right"],
                    $leftCode
                );

                break;
            }

            /**
             * From here, right part of expression is always resolved
             */
            if (isset($expr["right"])) {
                $rightCode = $this->expression($expr["right"], $doubleQuotes);
            }

            $exprCode = null;

            switch ($type) {
                case Enum::PHVOLT_T_NOT:
                    $exprCode = "!" . $rightCode;
                    break;

                case Enum::PHVOLT_T_MUL:
                    $exprCode = $leftCode . " * " . $rightCode;
                    break;

                case Enum::PHVOLT_T_ADD:
                    $exprCode = $leftCode . " + " . $rightCode;
                    break;

                case Enum::PHVOLT_T_SUB:
                    $exprCode = $leftCode . " - " . $rightCode;
                    break;

                case Enum::PHVOLT_T_DIV:
                    $exprCode = $leftCode . " / " . $rightCode;
                    break;

                case 37:
                    $exprCode = $leftCode . " % " . $rightCode;
                    break;

                case Enum::PHVOLT_T_LESS:
                    $exprCode = $leftCode . " < " . $rightCode;
                    break;

                case 61:
                case 62:
                    $exprCode = $leftCode . " > " . $rightCode;
                    break;

                case 126:
                    $exprCode = $leftCode . " . " . $rightCode;
                    break;

                case 278:
                    $exprCode = "pow(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case Enum::PHVOLT_T_ARRAY:
                    $exprCode = (isset($expr["left"])) ? "[" . $leftCode . "]" : "[]";
                    break;

                case 258:
                case 259:
                case Enum::PHVOLT_T_RESOLVED_EXPR:
                    $exprCode = $expr["value"];
                    break;

                case Enum::PHVOLT_T_STRING:
                    if ($doubleQuotes === false) {
                        $exprCode = "'" . str_replace(
                            "'",
                            "\\'",
                            $expr["value"]
                        ) . "'";
                    } else {
                        $exprCode = "\"" . $expr["value"] . "\"";
                    }
                    break;

                case Enum::PHVOLT_T_NULL:
                    $exprCode = "null";
                    break;

                case Enum::PHVOLT_T_FALSE:
                    $exprCode = "false";
                    break;

                case Enum::PHVOLT_T_TRUE:
                    $exprCode = "true";
                    break;

                case Enum::PHVOLT_T_IDENTIFIER:
                    $exprCode = '$' . $expr["value"];
                    break;

                case Enum::PHVOLT_T_AND:
                    $exprCode = $leftCode . " && " . $rightCode;
                    break;

                case 267:
                    $exprCode = $leftCode . " || " . $rightCode;
                    break;

                case Enum::PHVOLT_T_LESSEQUAL:
                    $exprCode = $leftCode . " <= " . $rightCode;
                    break;

                case 271:
                    $exprCode = $leftCode . " >= " . $rightCode;
                    break;

                case 272:
                    $exprCode = $leftCode . " == " . $rightCode;
                    break;

                case 273:
                    $exprCode = $leftCode . " != " . $rightCode;
                    break;

                case 274:
                    $exprCode = $leftCode . " === " . $rightCode;
                    break;

                case 275:
                    $exprCode = $leftCode . " !== " . $rightCode;
                    break;

                case Enum::PHVOLT_T_RANGE:
                    $exprCode = "range(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case Enum::PHVOLT_T_FCALL:
                    $exprCode = $this->functionCall($expr, $doubleQuotes);
                    break;

                case Enum::PHVOLT_T_ENCLOSED:
                    $exprCode = "(" . $leftCode . ")";
                    break;

                case Enum::PHVOLT_T_ARRAYACCESS:
                    $exprCode = $leftCode . "[" . $rightCode . "]";
                    break;

                case Enum::PHVOLT_T_SLICE:
                    /**
                     * Evaluate the start part of the slice
                     */
                    $startCode = isset($expr["start"]) ?
                        $this->expression($expr["start"], $doubleQuotes) :
                        'null';

                    /**
                     * Evaluate the end part of the slice
                     */
                    $endCode = isset($expr["end"]) ?
                        $this->expression($expr["end"], $doubleQuotes) :
                        'null';

                    $exprCode = '$this->slice('
                        . $leftCode . ", "
                        . $startCode . ", "
                        . $endCode . ")";
                    break;

                case Enum::PHVOLT_T_NOT_ISSET:
                    $exprCode = "!isset("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_ISSET:
                    $exprCode = "isset("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_NOT_ISEMPTY:
                    $exprCode = "!empty("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_ISEMPTY:
                    $exprCode = "empty("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_NOT_ISEVEN:
                    $exprCode = "!((("
                        . $leftCode
                        . ") % 2) == 0)";
                    break;

                case Enum::PHVOLT_T_ISEVEN:
                    $exprCode = "((("
                        . $leftCode
                        . ") % 2) == 0)";
                    break;

                case Enum::PHVOLT_T_NOT_ISODD:
                    $exprCode = "!((("
                        . $leftCode
                        . ") % 2) != 0)";
                    break;

                case Enum::PHVOLT_T_ISODD:
                    $exprCode = "((("
                        . $leftCode
                        . ") % 2) != 0)";
                    break;

                case Enum::PHVOLT_T_NOT_ISNUMERIC:
                    $exprCode = "!is_numeric("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_ISNUMERIC:
                    $exprCode = "is_numeric("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_NOT_ISSCALAR:
                    $exprCode = "!is_scalar("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_ISSCALAR:
                    $exprCode = "is_scalar("
                        . $leftCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_NOT_ISITERABLE:
                    $exprCode = "!(is_array("
                        . $leftCode
                        . ") || ("
                        . $leftCode
                        . ") instanceof Traversable)";
                    break;

                case Enum::PHVOLT_T_ISITERABLE:
                    $exprCode = "(is_array("
                        . $leftCode
                        . ") || ("
                        . $leftCode
                        . ") instanceof Traversable)";
                    break;

                case Enum::PHVOLT_T_IN:
                    $exprCode = '$this->isIncluded('
                        . $leftCode
                        . ", "
                        . $rightCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_NOT_IN:
                    $exprCode = '!$this->isIncluded('
                        . $leftCode
                        . ", "
                        . $rightCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_TERNARY:
                    $exprCode = "("
                        . $this->expression($expr["ternary"], $doubleQuotes)
                        . " ? "
                        . $leftCode
                        . " : "
                        . $rightCode
                        . ")";
                    break;

                case Enum::PHVOLT_T_MINUS:
                    $exprCode = "-" . $rightCode;
                    break;

                case Enum::PHVOLT_T_PLUS:
                    $exprCode = "+" . $rightCode;
                    break;

                default:
                    throw new Exception(
                        "Unknown expression "
                        . $type
                        . " in "
                        . $expr["file"]
                        . " on line "
                        . $expr["line"]
                    );
            }

            break;
        }

        $this->exprLevel--;

        return $exprCode;
    }

    /**
     * Fires an event to registered extensions
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    final public function fireExtensionEvent(string $name, array $arguments = []): mixed
    {
        foreach ($this->extensions as $extension) {
            /**
             * Check if the extension implements the required event name
             */
            if (method_exists($extension, $name)) {
                if (!empty($arguments)) {
                    $status = call_user_func_array(
                        [$extension, $name],
                        $arguments
                    );
                } else {
                    $status = call_user_func([$extension, $name]);
                }

                /**
                 * Only string statuses means the extension processes
                 * something
                 */
                if (is_string($status)) {
                    return $status;
                }
            }
        }

        return null;
    }

    /**
     * Resolves function intermediate code into PHP function calls
     *
     * @param array $expr
     * @param bool  $doubleQuotes
     *
     * @return string
     * @throws Exception
     */
    public function functionCall(array $expr, bool $doubleQuotes = false): string
    {
        $nameExpr = $expr["name"];
        $nameType = $nameExpr["type"];

        /**
         * The TagFactory helpers sometimes receive line endings
         * as parameters. Using single quotes is not going to make
         * that work. As such we need to recalculate the arguments
         */
        $arguments     = "";
        $funcArguments = $expr["arguments"] ?? null;
        if (isset($expr["arguments"])) {
            $arguments = $this->expression($funcArguments, $doubleQuotes);
        }

        /**
         * Check if it's a single function
         */
        if ($nameType == Enum::PHVOLT_T_IDENTIFIER) {
            $name = $nameExpr["value"];

            /**
             * Check if any of the registered extensions provide compilation for
             * this function
             */
            if (!empty($this->extensions)) {
                /**
                 * Notify the extensions about being compiling a function
                 */
                $code = $this->fireExtensionEvent(
                    "compileFunction",
                    [$name, $arguments, $funcArguments]
                );

                if (is_string($code)) {
                    return $code;
                }
            }

            /**
             * Check if it's a user defined function
             */
            if (!empty($this->functions) && isset($this->functions[$name])) {
                $definition = $this->functions[$name];

                /**
                 * Use the string as function
                 */
                if (is_string($definition)) {
                    return $definition . "(" . $arguments . ")";
                }

                /**
                 * Execute the function closure returning the compiled
                 * definition
                 */
                if ($definition instanceof Closure) {
                    return call_user_func_array(
                        $definition,
                        [$arguments, $funcArguments]
                    );
                }

                throw new Exception(
                    "Invalid definition for user function '"
                    . $name
                    . "' in "
                    . $expr["file"]
                    . " on line "
                    . $expr["line"]
                );
            }

            /**
             * This function includes the previous rendering stage
             */
            if ($name == "get_content" || $name == "content") {
                return '$this->getContent()';
            }

            /**
             * This function includes views of volt or others template engines
             * dynamically
             */
            if ($name == "partial") {
                return '$this->partial(' . $arguments . ")";
            }

            /**
             * This function embeds the parent block in the current block
             */
            if ($name == "super") {
                $extendedBlocks = $this->extendedBlocks;

                if (is_array($extendedBlocks)) {
                    $currentBlock = $this->currentBlock;

                    if (isset($extendedBlocks[$currentBlock])) {
                        $block     = $extendedBlocks[$currentBlock];
                        $exprLevel = $this->exprLevel;

                        if (is_array($block)) {
                            $code = $this->statementListOrExtends($block);

                            if ($exprLevel == 1) {
                                $escapedCode = $code;
                            } else {
                                $escapedCode = addslashes($code);
                            }
                        } else {
                            if ($exprLevel == 1) {
                                $escapedCode = $block;
                            } else {
                                $escapedCode = addslashes($block);
                            }
                        }

                        /**
                         * If the super() is the first level we don't escape it
                         */
                        if ($exprLevel == 1) {
                            return $escapedCode;
                        }

                        return "'" . $escapedCode . "'";
                    }
                }

                return "''";
            }

            /**
             * Check if it's a method in Phalcon\Tag
             *
             * @todo This needs a lot of refactoring and will break a lot of applications if removed
             */
            if ($name === "preload") {
                return '$this->preload(' . $arguments . ")";
            }

            /**
             * Check if it's a method in Phalcon\Tag
             *
             * @todo This needs a lot of refactoring and will break a lot of applications if removed
             */
            $method       = lcfirst($this->toCamelize($name));
            $arrayHelpers = [
                'link_to'        => true,
                'image'          => true,
                'form_legacy'    => true,
                'submit_button'  => true,
                'radio_field'    => true,
                'check_field'    => true,
                'file_field'     => true,
                'hidden_field'   => true,
                'password_field' => true,
                'text_area'      => true,
                'text_field'     => true,
                'email_field'    => true,
                'date_field'     => true,
                'tel_field'      => true,
                'numeric_field'  => true,
                'image_input'    => true,
            ];

            if (method_exists("Phalcon\\Tag", $method)) {
                if (isset($arrayHelpers[$name])) {
                    return "\Phalcon\Tag::" . $method . "([" . $arguments . "])";
                }

                return "\Phalcon\Tag::" . $method . "(" . $arguments . ")";
            }

            /**
             * These are for the TagFactory
             */
            if (null !== $this->container && true === $this->container->has("tag")) {
                $tagService = $this->container->get("tag");
                if (true === $tagService->has($name)) {
                    /**
                     * recalculate the arguments because we need them double
                     * quoted
                     */
                    $arguments     = "";
                    $funcArguments = $expr["arguments"] ?? null;
                    if (isset($expr["arguments"])) {
                        $arguments = $this->expression($funcArguments, true);
                    }

                    return '$this->tag->' . $name . "(" . $arguments . ")";
                }
            }

            /**
             * Get a dynamic URL
             */
            if ($name == "url") {
                return '$this->url->get(' . $arguments . ')';
            }

            /**
             * Get a static URL
             */
            if ($name == "static_url") {
                return '$this->url->getStatic(' . $arguments . ")";
            }

            if ($name == "date") {
                return "date(" . $arguments . ")";
            }

            if ($name == "time") {
                return "time()";
            }

            if ($name == "dump") {
                return "var_dump(" . $arguments . ")";
            }

            if ($name == "version") {
                return "(new Phalcon\\Support\\Version)->get()";
            }

            if ($name == "version_id") {
                return "(new Phalcon\\Support\\Version)->getId()";
            }

            if ($name == "preload") {
                return '$this->preload(' . $arguments . ")";
            }

            /**
             * Read PHP constants in templates
             */
            if ($name == "constant") {
                return "constant(" . $arguments . ")";
            }

            /**
             * By default it tries to call a macro
             */
            return '$' . "this->callMacro('" . $name . "', [" . $arguments . "])";
        }

        return $this->expression($nameExpr, $doubleQuotes) . "(" . $arguments . ")";
    }

    /**
     * Returns the path to the last compiled template
     *
     * @return string
     */
    public function getCompiledTemplatePath(): string
    {
        return $this->compiledTemplatePath;
    }

    /**
     * Returns the list of extensions registered in Volt
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Register the user registered filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Register the user registered functions
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * Returns a compiler's option
     *
     * @param string $option
     *
     * @return string|null
     */
    public function getOption(string $option): string | null
    {
        return $this->options[$option] ?? null;
    }

    /**
     * Returns the compiler options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the path that is currently being compiled
     *
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->currentPath;
    }

    /**
     * Return a unique prefix to be used as prefix for compiled variables and
     * contexts
     *
     * @return string
     * @throws Exception
     */
    public function getUniquePrefix(): string
    {
        /**
         * If the unique prefix is not set we use a hash using the modified
         * Berstein algorithm
         */
        if (!$this->prefix) {
            $this->prefix = $this->getUniquePathKey($this->currentPath);
        }

        /**
         * The user could use a closure generator
         */
        if (is_object($this->prefix) && $this->prefix instanceof Closure) {
            $this->prefix = call_user_func_array(
                $this->prefix,
                [
                    $this,
                ]
            );
        }

        if (!is_string($this->prefix)) {
            throw new Exception("The unique compilation prefix is invalid");
        }

        return $this->prefix;
    }


    /**
     * Parses a Volt template returning its intermediate representation
     *
     *```php
     * print_r(
     *     $compiler->parse("{% raw %}{{ 3 + 2 }}{% endraw %}")
     * );
     *```
     *
     * @throws Exception
     */
    public function parse(string $viewCode): array
    {
        return (new Parser($viewCode))->parseView("eval code");
    }

    /**
     * Resolves filter intermediate code into a valid PHP expression
     *
     * @param array  $test
     * @param string $left
     *
     * @return string
     * @throws Exception
     */
    public function resolveTest(array $test, string $left): string
    {
        $type = $test["type"];

        /**
         * Check if right part is a single identifier
         */
        if ($type == Enum::PHVOLT_T_IDENTIFIER) {
            $name = $test["value"];

            switch ($name) {
                case "empty":
                    return "empty(" . $left . ")";
                case "even":
                    return "(((" . $left . ") % 2) == 0)";
                case "odd":
                    return "(((" . $left . ") % 2) != 0)";
                case "numeric":
                    return "is_numeric(" . $left . ")";
                case "scalar":
                    return "is_scalar(" . $left . ")";
                case "iterable":
                    return "(is_array(" . $left . ") || (" . $left . ") instanceof Traversable)";
            }
        }

        /**
         * Check if right part is a function call
         */
        if ($type == Enum::PHVOLT_T_FCALL) {
            $testName = $test["name"];

            if (isset($testName["value"])) {
                $name = $testName["value"];
                switch ($name) {
                    case "divisibleby":
                        return "((("
                            . $left
                            . ") % ("
                            . $this->expression($test["arguments"])
                            . ")) == 0)";
                    case "sameas":
                        return "("
                            . $left
                            . ") === ("
                            . $this->expression($test["arguments"])
                            . ")";
                    case "type":
                        return "gettype("
                            . $left
                            . ") === ("
                            . $this->expression($test["arguments"])
                            . ")";
                }
            }
        }

        /**
         * Fall back to the equals operator
         */
        return $left . " == " . $this->expression($test);
    }

    /**
     * Sets a single compiler option
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption(string $option, mixed $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets the compiler options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set a unique prefix to be used as prefix for compiled variables
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setUniquePrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Compiles a Volt source code returning a PHP plain version
     *
     * @param string $viewCode
     * @param bool   $extendsMode
     *
     * @return array|string
     * @throws Exception
     */
    protected function compileSource(
        string $viewCode,
        bool $extendsMode = false
    ): array | string {
        /**
         * Check for compilation options
         */
        if (!empty($this->options) && isset($this->options["autoescape"])) {
            /**
             * Enable autoescape globally
             */
            $autoescape = $this->options["autoescape"];
            if (!is_bool($autoescape)) {
                throw new Exception("'autoescape' must be bool");
            }

            $this->autoescape = $autoescape;
        }

        $intermediate = (new Parser($viewCode))->parseView($this->currentPath);
        $compilation  = $this->statementList($intermediate, $extendsMode);

        /**
         * Check if the template is extending another
         */
        $extended = $this->extended;

        if ($extended === true) {
            /**
             * Multiple-Inheritance is allowed
             */
            if ($extendsMode === true) {
                $finalCompilation = [];
            } else {
                $finalCompilation = null;
            }

            $blocks         = $this->blocks;
            $extendedBlocks = $this->extendedBlocks;

            foreach ($extendedBlocks as $name => $block) {
                /**
                 * If name is a string then is a block name
                 */
                if (is_string($name)) {
                    if (array_key_exists($name, $blocks)) {
                        /**
                         * The block is set in the local template
                         */
                        $localBlock         = $blocks[$name];
                        $this->currentBlock = $name;

                        /**
                         * This is to ensure in PHP 8.0 we pass an array to statementList
                         */
                        if (null === $localBlock) {
                            $localBlock = [];
                        }

                        $blockCompilation = $this->statementList($localBlock);
                    } else {
                        if (is_array($block)) {
                            /**
                             * The block is not set local only in the extended
                             * template
                             */
                            $blockCompilation = $this->statementList($block);
                        } else {
                            $blockCompilation = $block;
                        }
                    }

                    if ($extendsMode) {
                        $finalCompilation[$name] = $blockCompilation;
                    } else {
                        $finalCompilation .= $blockCompilation;
                    }
                } else {
                    /**
                     * Here the block is an already compiled text
                     */
                    if ($extendsMode) {
                        $finalCompilation[] = $block;
                    } else {
                        $finalCompilation .= $block;
                    }
                }
            }

            return $finalCompilation;
        }

        if ($extendsMode) {
            /**
             * In extends mode we return the template blocks instead of the
             * compilation
             */
            return $this->blocks;
        }

        return $compilation;
    }

    /**
     * Gets the final path with VIEW
     *
     * @param string $path
     *
     * @return string
     */
    protected function getFinalPath(string $path): string
    {
        if (null !== $this->view) {
            $viewsDirs = $this->view->getViewsDir();

            if (is_array($viewsDirs)) {
                foreach ($viewsDirs as $viewsDir) {
                    if (file_exists($viewsDir . $path)) {
                        return $viewsDir . $path;
                    }
                }
            }

            return $viewsDirs . $path;
        }

        return $path;
    }

    /**
     * Resolves filter intermediate code into PHP function calls
     *
     * @param array  $filter
     * @param string $left
     *
     * @return string
     * @throws Exception
     */
    final protected function resolveFilter(array $filter, string $left): string
    {
        $type = $filter["type"];

        /**
         * Check if the filter is a single identifier
         */
        if ($type == Enum::PHVOLT_T_IDENTIFIER) {
            $name = $filter["value"];
        } else {
            if ($type != Enum::PHVOLT_T_FCALL) {
                /**
                 * Unknown filter throw an exception
                 */
                throw new Exception(
                    "Unknown filter type in "
                    . $filter["file"]
                    . " on line "
                    . $filter["line"]
                );
            }

            $functionName = $filter["name"];
            $name         = $functionName["value"];
        }

        $funcArguments = null;

        /**
         * Resolve arguments
         */
        $arguments = $left;
        if (isset($filter["arguments"])) {
            $funcArguments = $filter["arguments"];

            /**
             * "default" filter is not the first argument, improve this!
             */
            if ($name !== "default") {
                $file = $filter["file"];
                $line = $filter["line"];

                /**
                 * TODO: Implement this function directly
                 */
                array_unshift(
                    $funcArguments,
                    [
                        "expr" => [
                            "type"  => 364,
                            "value" => $left,
                            "file"  => $file,
                            "line"  => $line,
                        ],
                        "file" => $file,
                        "line" => $line,
                    ]
                );
            }

            $arguments = $this->expression($funcArguments);
        }

        /**
         * Check if any of the registered extensions provide compilation for
         * this filter
         */
        if (!empty($this->extensions)) {
            /**
             * Notify the extensions about being compiling a function
             */
            $code = $this->fireExtensionEvent(
                "compileFilter",
                [$name, $arguments, $funcArguments]
            );

            if (is_string($code)) {
                return $code;
            }
        }

        /**
         * Check if it's a user defined filter
         */
        if (isset($this->filters[$name])) {
            $definition = $this->filters[$name];

            /**
             * The definition is a string
             */
            if (is_string($definition)) {
                return $definition . "(" . $arguments . ")";
            }

            /**
             * The definition is a closure
             */
            if ($definition instanceof Closure) {
                return call_user_func_array(
                    $definition,
                    [$arguments, $funcArguments]
                );
            }

            /**
             * Invalid filter definition throw an exception
             */
            throw new Exception(
                "Invalid definition for user filter '"
                . $name . "' in "
                . $filter["file"] . " on line "
                . $filter["line"]
            );
        }

        switch ($name) {
            case "abs":
                return "abs(" . $arguments . ")";
            case "capitalize":
                return "ucwords(" . $arguments . ")";
            case "convert_encoding":
                return '$this->convertEncoding(' . $arguments . ")";
            case "default":
                return "(empty(" . $left . ") ? ("
                    . $arguments . ") : ("
                    . $left . "))";
            case "e":
            case "escape":
                return '$this->escaper->html(' . $arguments . ")";
            case "escape_attr":
                return '$this->escaper->attributes(' . $arguments . ")";
            case "escape_css":
                return '$this->escaper->css(' . $arguments . ")";
            case "escape_js":
                return '$this->escaper->js(' . $arguments . ")";
            case "format":
                return "sprintf(" . $arguments . ")";
            case "join":
                return "join('" . $funcArguments[1]["expr"]["value"]
                    . "', " . $funcArguments[0]["expr"]["value"] . ")";
            case "json_encode":
                return "json_encode(" . $arguments . ")";
            case "json_decode":
                return "json_decode(" . $arguments . ")";
            case "keys":
                return "array_keys(" . $arguments . ")";
            case "left_trim":
                return "ltrim(" . $arguments . ")";
            case "length":
                return '$this->length(' . $arguments . ")";
            case "lower":
            case "lowercase":
                if (
                    null !== $this->container &&
                    true === $this->container->has("helper")
                ) {
                    return '$this->helper->lower(' . $arguments . ")";
                } else {
                    return "strtolower(" . $arguments . ")";
                }
            case "right_trim":
                return "rtrim(" . $arguments . ")";
            case "nl2br":
                return "nl2br(" . $arguments . ")";
            case "slashes":
                return "addslashes(" . $arguments . ")";
            case "slice":
                return '$this->slice(' . $arguments . ")";
            case "sort":
                return '$this->sort(' . $arguments . ")";
            case "stripslashes":
                return "stripslashes(" . $arguments . ")";
            case "striptags":
                return "strip_tags(" . $arguments . ")";
            case "trim":
                return "trim(" . $arguments . ")";
            case "upper":
            case "uppercase":
                if (
                    null !== $this->container &&
                    true === $this->container->has("helper")
                ) {
                    return '$this->helper->upper(' . $arguments . ")";
                } else {
                    return "strtoupper(" . $arguments . ")";
                }
            case "url_encode":
                return "urlencode(" . $arguments . ")";
            default:
                throw new Exception(
                    'Unknown filter "' . $name . '" in '
                    . $filter["file"] . ' on line ' . $filter["line"]
                );
        }
    }

    /**
     * Traverses a statement list compiling each of its nodes
     *
     * @param array $statements
     * @param bool  $extendsMode
     *
     * @return string | null
     * @throws Exception
     */
    final protected function statementList(
        array $statements,
        bool $extendsMode = false
    ): string | null {
        /**
         * Nothing to compile
         */
        if (empty($statements)) {
            return "";
        }

        /**
         * Increase the statement recursion level in extends mode
         */
        $blockMode = $this->extended || $extendsMode;

        if ($blockMode === true) {
            $this->blockLevel++;
        }

        $this->level++;

        $compilation = null;
        foreach ($statements as $statement) {
            /**
             * All statements must be arrays
             */
            if (!is_array($statement)) {
                throw new Exception("Corrupted statement");
            }

            /**
             * Check if the statement is valid
             */
            if (!isset($statement["type"])) {
                throw new Exception(
                    "Invalid statement in "
                    . $statement["file"] . " on line "
                    . $statement["line"],
                    $statement
                );
            }

            /**
             * Check if extensions have implemented custom compilation for this
             * statement
             */
            if (!empty($this->extensions)) {
                /**
                 * Notify the extensions about being resolving a statement
                 */
                $tempCompilation = $this->fireExtensionEvent(
                    "compileStatement",
                    [$statement]
                );

                if (is_string($tempCompilation)) {
                    $compilation .= $tempCompilation;

                    continue;
                }
            }

            /**
             * Get the statement type
             */
            $type = $statement["type"];

            /**
             * Compile the statement according to the statement's type
             */
            switch ($type) {
                case Enum::PHVOLT_T_RAW_FRAGMENT:
                    $compilation .= $statement["value"];
                    break;

                case Enum::PHVOLT_T_IF:
                    $compilation .= $this->compileIf($statement, $extendsMode);
                    break;

                case Enum::PHVOLT_T_ELSEIF:
                    $compilation .= $this->compileElseIf($statement);
                    break;

                case Enum::PHVOLT_T_SWITCH:
                    $compilation .= $this->compileSwitch(
                        $statement,
                        $extendsMode
                    );

                    break;

                case Enum::PHVOLT_T_CASE:
                    $compilation .= $this->compileCase($statement);
                    break;

                case Enum::PHVOLT_T_DEFAULT:
                    $compilation .= $this->compileCase($statement, false);
                    break;

                case Enum::PHVOLT_T_FOR:
                    $compilation .= $this->compileForeach(
                        $statement,
                        $extendsMode
                    );

                    break;

                case Enum::PHVOLT_T_SET:
                    $compilation .= $this->compileSet($statement);
                    break;

                case Enum::PHVOLT_T_ECHO:
                    $compilation .= $this->compileEcho($statement);
                    break;

                case Enum::PHVOLT_T_BLOCK:
                    /**
                     * Block statement
                     */
                    $blockName       = $statement["name"];
                    $blockStatements = $statement["block_statements"] ?? null;
                    $blocks          = $this->blocks;

                    if ($blockMode) {
                        if (!is_array($blocks)) {
                            $blocks = [];
                        }

                        /**
                         * Create a unamed block
                         */
                        if ($compilation !== null) {
                            $blocks[]    = $compilation;
                            $compilation = null;
                        }

                        /**
                         * In extends mode we add the block statements to the
                         * blocks variable
                         */
                        $blocks[$blockName] = $blockStatements;
                        $this->blocks       = $blocks;
                    } else {
                        if (is_array($blockStatements)) {
                            $compilation .= $this->statementList(
                                $blockStatements,
                                $extendsMode
                            );
                        }
                    }

                    break;

                case Enum::PHVOLT_T_EXTENDS:
                    /**
                     * Extends statement
                     */
                    $path      = $statement["path"];
                    $finalPath = $this->getFinalPath($path["value"]);
                    $extended  = true;

                    /**
                     * Perform a sub-compilation of the extended file
                     */
                    $subCompiler     = clone $this;
                    $tempCompilation = $subCompiler->compile(
                        $finalPath,
                        $extended
                    );

                    /**
                     * If the compilation doesn't return anything we include the
                     * compiled path
                     */
                    if ($tempCompilation === null) {
                        $tempCompilation = file_get_contents(
                            $subCompiler->getCompiledTemplatePath()
                        );
                    }

                    $this->extended       = true;
                    $this->extendedBlocks = $tempCompilation;
                    $blockMode            = $extended;

                    break;

                case Enum::PHVOLT_T_INCLUDE:
                    $compilation .= $this->compileInclude($statement);

                    break;

                case Enum::PHVOLT_T_DO:
                    $compilation .= $this->compileDo($statement);
                    break;

                case Enum::PHVOLT_T_RETURN:
                    $compilation .= $this->compileReturn($statement);
                    break;

                case Enum::PHVOLT_T_AUTOESCAPE:
                    $compilation .= $this->compileAutoEscape(
                        $statement,
                        $extendsMode
                    );

                    break;

                case Enum::PHVOLT_T_CONTINUE:
                    /**
                     * "Continue" statement
                     */
                    $compilation .= "<?php continue; ?>";
                    break;

                case Enum::PHVOLT_T_BREAK:
                    /**
                     * "Break" statement
                     */
                    $compilation .= "<?php break; ?>";
                    break;

                case 321:
                    /**
                     * "Forelse" condition
                     */
                    $compilation .= $this->compileForElse();
                    break;

                case Enum::PHVOLT_T_MACRO:
                    /**
                     * Define a macro
                     */
                    $compilation .= $this->compileMacro(
                        $statement,
                        $extendsMode
                    );

                    break;

                case 325:
                    /**
                     * "Call" statement
                     */
                    $compilation .= $this->compileCall(
                        $statement,
                        $extendsMode
                    );

                    break;

                case 358:
                    /**
                     * Empty statement
                     */
                    break;

                default:
                    throw new Exception(
                        "Unknown statement " . $type . " in "
                        . $statement["file"] . " on line "
                        . $statement["line"]
                    );
            }
        }

        /**
         * Reduce the statement level nesting
         */
        if ($blockMode === true) {
            $level = $this->blockLevel;

            if (
                $level == 1 &&
                $compilation !== null
            ) {
                $this->blocks[] = $compilation;
            }

            $this->blockLevel--;
        }

        $this->level--;

        return $compilation;
    }

    /**
     * Compiles a block of statements
     *
     * @param mixed $statements
     *
     * @return mixed
     * @throws Exception
     */
    final protected function statementListOrExtends(mixed $statements): mixed
    {
        /**
         * Resolve the statement list as normal
         */
        if (!is_array($statements)) {
            return $statements;
        }

        /**
         * If all elements in the statement list are arrays we resolve this as a
         * statementList
         */
        $isStatementList = true;
        if (!isset($statements["type"])) {
            foreach ($statements as $statement) {
                if (!is_array($statement)) {
                    $isStatementList = false;

                    break;
                }
            }
        }

        /**
         * Resolve the statement list as normal
         */
        if ($isStatementList) {
            return $this->statementList($statements);
        }

        /**
         * Is an array but not a statement list?
         */
        return $statements;
    }

    /**
     * @param string|null $path
     *
     * @return string
     */
    private function getUniquePathKey(string | null $path): string
    {
        if ($path) {
            return "v" . hash('crc32b', $path);
        }

        return '';
    }

    /**
     * @param array $expression
     *
     * @return bool
     */
    private function isTagFactory(array $expression): bool
    {
        /**
         * This will check recursively:
         * - If we have a "name" array.
         * - If the "name" has a "left" sub-array
         * - If the "left" sub-array has a "value" of "tag"
         * - If the "left" has another sub-array then recurse
         */
        if (isset($expression["name"])) {
            $name = $expression["name"];
            if (isset($name["left"])) {
                $left = $name["left"];

                /**
                 * There is a value, get it and check it
                 */
                if (isset($left["value"])) {
                    return ($left["value"] === "tag");
                } else {
                    /**
                     * There is a "name" so that is nested, recursion
                     */
                    if (isset($left["name"]) && is_array($left["name"])) {
                        return $this->isTagFactory($left);
                    }
                }
            }
        }

        return false;
    }
}
