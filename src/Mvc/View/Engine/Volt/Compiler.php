<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\View\Engine\Volt;

use Closure;
use Phalcon\Di\DiInterface;
use Phalcon\Mvc\ViewBaseInterface;
use Phalcon\Di\InjectionAwareInterface;

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
    protected $autoescape = false;
    protected $blockLevel = 0;
    protected $blocks;
    protected $container;
    protected $compiledTemplatePath;
    protected $currentBlock;
    protected $currentPath;
    protected $exprLevel = 0;
    protected $extended = false;
    protected $extensions;
    protected $extendedBlocks;
    protected $filters;
    protected $foreachLevel = 0;
    protected $forElsePointers;
    protected $functions;
    protected $level = 0;
    protected $loopPointers;
    protected $macros;
    protected $options;
    protected $prefix;
    protected $view;

    /**
     * Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public function __construct(ViewBaseInterface $view = null)
    {
        $this->view = $view;
    }

    /**
     * Registers a Volt's extension
     */
    public function addExtension($extension) : Compiler
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
     */
    public function addFilter(string $name, mixed $definition) : Compiler
    {
        $this->filters[$name] = $definition;

        return $this;
    }

    /**
     * Register a new function in the compiler
     */
    public function addFunction(string  $name, mixed $definition) : Compiler
    {
        $this->functions[$name] = $definition;

        return $this;
    }

    /**
     * Resolves attribute reading
     */
    public function attributeReader(array $expr) : string
    {
        $exprCode = "";

        $left = $expr["left"];

        if ($left["type"] === PHVOLT_T_IDENTIFIER) {
            $variable = $left["value"];

            /**
             * Check if the variable is the loop context
             */
            if ($variable === "loop") {
                $level = $this->foreachLevel;
                $exprCode .= "\$" . $this->getUniquePrefix() . $level . "loop";
                $this->loopPointers[$level] = $level;
            } else {
                /**
                 * Services registered in the dependency injector container are
                 * available always
                 */
                if (is_object($this->container) && $this->container->has($variable)) {
                    $exprCode .= "\$this->" . $variable;
                } else {
                    $exprCode .= "\$" . $variable;
                }
            }
        } else {
            $leftCode = $this->expression($left);
            $leftType = $left["type"];

            if ($leftType !== PHVOLT_T_DOT && $leftType !== PHVOLT_T_FCALL) {
                $exprCode .= $leftCode;
            } else {
                $exprCode .= $leftCode;
            }
        }

        $exprCode .= "->";

        $right = $expr["right"];

        if ($right["type"] === PHVOLT_T_IDENTIFIER) {
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
     */
    public function compile(string $templatePath, bool $extendsMode = false)
    {
        /**
         * Re-initialize some properties already initialized when the object is
         * cloned
         */
        $this->extended = false;
        $this->extendedBlocks = false;
        $this->blocks = null;
        $this->level = 0;
        $this->foreachLevel = 0;
        $this->blockLevel = 0;
        $this->exprLevel = 0;

        $compilation = null;

        $options = $this->options;

        /**
         * This makes that templates will be compiled always
         */
        $compileAlways = $options["always"] ?? false;
        if (!$compileAlways) {
        	$compileAlways = $options["compileAlways"] ?? null;
            if ($compileAlways !== null) {
                trigger_error(
                    "The 'compileAlways' option is deprecated. Use 'always' instead.",
                    E_USER_DEPRECATED
                );
            } else {
                $compileAlways = false;
            }
        }

        if (!is_bool($compileAlways)) {
            throw new Exception("'always' must be a bool value");
        }

        /**
         * Prefix is prepended to the template name
         */
        $prefix = $options["prefix"] ?? "";

        if (!is_string($prefix)) {
            throw new Exception("'prefix' must be a string");
        }

        /**
         * Compiled path is a directory where the compiled templates will be
         * located
         */
        $compiledPath = $options["path"] ?? null;
        if ($compiledPath === null){
            $compiledPath = $options["compiledPath"] ?? null;
            if ($compilePath !== null) {
                trigger_error(
                    "The 'compiledPath' option is deprecated. Use 'path' instead.",
                    E_USER_DEPRECATED
                );
            } else {
                $compiledPath = "";
            }
        }

        /**
         * There is no compiled separator by default
         */
        $compiledSeparator = $options["separator"] ?? null;
        if ($compiledSeparator === null) {
            $compiledSeparator = $options["compiledSeparator"] ?? null;
            if ($compiledSeparator !== null) {
                trigger_error(
                    "The 'compiledSeparator' option is deprecated. Use 'separator' instead.",
                    E_USER_DEPRECATED
                );
            } else {
                $compiledSeparator = "%%";
            }
        }

        if (!is_string($compiledSeparator)) {
            throw new Exception("'separator' must be a string");
        }

        /**
         * By default the compile extension is .php
         */
        $compiledExtension = $options["extension"] ?? null;
        if ($compiledExtension === null) {
            $compiledExtension = $options["compiledExtension"] ?? null;
            if ($compiledExtension !== null) {
                trigger_error(
                    "The 'compiledExtension' option is deprecated. Use 'extension' instead.",
                    E_USER_DEPRECATED
                );
            } else {
                $compiledExtension = ".php";
            }
        }

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
                $templateSepPath = prepare_virtual_path(
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
                $compiledTemplatePath = $compiledPath . $prefix . $templateSepPath . $compiledSeparator . "e" . $compiledSeparator . $compiledExtension;
            } else {
                $compiledTemplatePath = $compiledPath . $prefix . $templateSepPath . $compiledExtension;
            }
        } elseif (is_object($compiledPath) && $compiledPath instanceof Closure) {
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
             * needs to $compiled every time
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
                if (compare_mtime($templatePath, $compiledTemplatePath)) {
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
                                "Extends compilation file " . $compiledTemplatePath . " could not be opened"
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
     */
    public function compileAutoEscape(array $statement, bool $extendsMode) : string
    {
        /**
         * A valid option is required
         */
        $autoescape = $statement["enable"] ?? null;
        if ($autoescape === null) {
            throw new Exception("Corrupted statement");
        }

        /**
         * "autoescape" mode
         */
        $oldAutoescape = $this->autoescape;
        $this->autoescape = $autoescape;

        $compilation = $this->statementList(
            $statement["block_statements"],
            $extendsMode
        );

        $this->autoescape = $oldAutoescape;

        return $compilation;
    }

    /**
     * Compiles a "cache" statement returning PHP code
     *
     * @deprecated Will be removed in 5.0
     * @todo Remove this in the next major version
     */
    public function compileCache(array $statement, bool $extendsMode = false) : string
    {
        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if ($expr === null) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * Cache statement
         */
        $exprCode = $this->expression($expr);

        $compilation = "<?php \$_cache[" . $this->expression($expr) . "] = \$this->di->get('viewCache'); ";
        $lifetime = $statement["lifetime"] ?? false;
        if (is_array($lifetime)) {
            $compilation .= "\$_cacheKey\[" . $exprCode . "]";

            if ($lifetime["type"] === PHVOLT_T_IDENTIFIER) {
                $compilation .= " = \$_cache[" . $exprCode . "]->start(" . $exprCode . ", \$" . $lifetime["value"] . "); ";
            } else {
                $compilation .= " = \$_cache[" . $exprCode . "]->start(" . $exprCode . ", " . $lifetime["value"] . "); ";
            }
        } else {
            $compilation .= "\$_cacheKey[" . $exprCode . "] = \$_cache[" . $exprCode."]->start(" . $exprCode . "); ";
        }

        $compilation .= "if (\$_cacheKey[" . $exprCode . "] === null) { ?>";

        /**
         * Get the code in the block
         */
        $compilation .= $this->statementList(
            $statement["block_statements"],
            $extendsMode
        );

        /**
         * Check if the cache has a lifetime
         */
        $lifetime = $statement["lifetime"] ?? false;
        if (is_array($lifetime)) {
            if ($lifetime["type"] === PHVOLT_T_IDENTIFIER) {
                $compilation .= "<?php \$_cache[" . $exprCode . "]->save(" . $exprCode . ", null, $" . $lifetime["value"] . "); ";
            } else {
                $compilation .= "<?php \$_cache[" . $exprCode . "]->save(" . $exprCode . ", null, " . $lifetime["value"] . "); ";
            }

            $compilation .= "} else { echo \$_cacheKey[" . $exprCode . "]; } ?>";
        } else {
            $compilation .= "<?php \$_cache[" . $exprCode . "]->save(" . $exprCode . "); } else { echo \$_cacheKey[" . $exprCode . "]; } ?>";
        }

        return $compilation;
    }

    /**
     * Compiles calls to macros
     */
    public function compileCall(array $statement, bool $extendsMode)
    {

    }

    /**
     * Compiles a "case"/"default" clause returning PHP code
     */
    public function compileCase(array $statement, bool $caseClause = true) : string
    {
        if ($caseClause === false) {
            /**
             * "default" statement
             */
            return "<?php default: ?>";
        }

        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if ($expr === null) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * "case" statement
         */
        return "<?php case " . $this->expression($expr) . ": ?>";
    }

    /**
     * Compiles a "do" statement returning PHP code
     */
    public function compileDo(array $statement) : string
    {
        /**
         * A valid expression is required
         */
        $expr = $statement["expr"]  ?? null;
        if ($expr === null){
            throw new Exception("Corrupted statement");
        }

        /**
         * "Do" statement
         */
        return "<?php " . $this->expression($expr) . "; ?>";
    }

    /**
     * Compiles a {% raw %}`{{` `}}`{% endraw %} statement returning PHP code
     */
    public function compileEcho(array $statement) : string
    {

        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if ($expr === null) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * Evaluate common expressions
         */
        $exprCode = $this->expression($expr);

        if ($expr["type"] === PHVOLT_T_FCALL ) {
            $name = $expr["name"];

            if ($name["type"] === PHVOLT_T_IDENTIFIER) {
                /**
                 * super() is a function however the return of this function
                 * must be output as it is
                 */
                if ($name["value"] === "super") {
                    return $exprCode;
                }
            }
        }

        /**
         * Echo statement
         */
        if ($this->autoescape) {
            return "<?= $this->escaper->escapeHtml(" . $exprCode . ") ?>";
        }

        return "<?= " . $exprCode . " ?>";
    }

    /**
     * Compiles a "elseif" statement returning PHP code
     */
    public function compileElseIf(array $statement) : string
    {
        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if ($expr === null) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * "elseif" statement
         */
        return "<?php } elseif (" . $this->expression($expr) . ") { ?>";
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
     * @return string|array
     */
    public function compileFile(string $path, string $compiledPath, 
            bool $extendsMode = false)
    {

        if ($path === $compiledPath) {
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
        if (is_array($compilation)) {
            $finalCompilation = serialize($compilation);
        } else {
            $finalCompilation = $compilation;
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
     * Compiles a "foreach" intermediate code representation into plain PHP code
     */
    public function compileForeach(array $statement, bool $extendsMode = false) : string
    {

        /**
         * A valid expression is required
         */
        if (!isset($statement["expr"])){
            throw new Exception("Corrupted statement");
        }

        $compilation = "";
        $forElse = null;

        $this->foreachLevel++;

        $prefix = $this->getUniquePrefix();

        $level = $this->foreachLevel;

        /**
         * prefixLevel is used to prefix every temporal variable
         */
        $prefixLevel = $prefix . $level;

        /**
         * Evaluate common expressions
         */
        $expr = $statement["expr"];

        $exprCode = $this->expression($expr);

        /**
         * Process the block statements
         */
        $blockStatements = $statement["block_statements"];

        $forElse = false;

        if (is_array($blockStatements)) {
            foreach ($blockStatements as $statement) {
                /**
                 * Check if the statement is valid
                 */
                $type = $statement["type"] ?? null;
                if ( $type === null) {
                    break;
                }

                if ($type === PHVOLT_T_ELSEFOR) {
                    $compilation .= "<?php \$" . $prefixLevel . "iterated = false; ?>";
                    $forElse = $prefixLevel;
                    $this->forElsePointers[$level] = $forElse;

                    break;
                }
            }
        }

        /**
         * Process statements block
         */
        $code = $this->statementList($blockStatements, $extendsMode);

        $loopContext = $this->loopPointers;

        /**
         * Generate the loop context for the "foreach"
         */
        if (isset($loopContext[$level])){
            $compilation .= "<?php \$" . $prefixLevel . "iterator = " . $exprCode . "; ";
            $compilation .= "\$" . $prefixLevel . "incr = 0; ";
            $compilation .= "\$" . $prefixLevel . "loop = new stdClass(); ";
            $compilation .= "\$" . $prefixLevel . "loop->self = &\$" . $prefixLevel . "loop; ";
            $compilation .= "\$" . $prefixLevel . "loop->length = count(\$" . $prefixLevel . "iterator); ";
            $compilation .= "\$" . $prefixLevel . "loop->index = 1; ";
            $compilation .= "\$" . $prefixLevel . "loop->index0 = 1; ";
            $compilation .= "\$" . $prefixLevel . "loop->revindex = \$" . $prefixLevel . "loop->length; ";
            $compilation .= "\$" . $prefixLevel . "loop->revindex0 = \$" . $prefixLevel . "loop->length - 1; ?>";

            $iterator = "\$" . $prefixLevel . "iterator";
        } else {
            $iterator = $exprCode;
        }

        /**
         * Foreach statement
         */
        $variable = $statement["variable"];

        /**
         * Check if a "key" variable needs to be calculated
         */
        $key = $statement["key"] ?? null;
        if ($key !== null) {
            $compilation .= "<?php foreach (" . $iterator . " as \$" . $key . " => \$" . $variable . ") { ";
        } else {
            $compilation .= "<?php foreach (" . $iterator . " as \$" . $variable . ") { ";
        }

        /**
         * Check for an "if" expr in the block
         */
        $ifExpr = $statement["if_expr"] ?? null;
        if ($ifExpr !== null) {
            $compilation .= "if (" . $this->expression($ifExpr) . ") { ?>";
        } else {
            $compilation .= "?>";
        }

        /**
         * Generate the loop context inside the cycle
         */
        if (isset($loopContext[$level])) {
            $compilation .= "<?php \$" . $prefixLevel . "loop->first = (\$" . $prefixLevel . "incr == 0); ";
            $compilation .= "\$" . $prefixLevel . "loop->index = $" . $prefixLevel . "incr + 1; ";
            $compilation .= "\$" . $prefixLevel . "loop->index0 = $" . $prefixLevel . "incr; ";
            $compilation .= "\$" . $prefixLevel . "loop->revindex = $" . $prefixLevel . "loop->length - \$" . $prefixLevel . "incr; ";
            $compilation .= "\$" . $prefixLevel . "loop->revindex0 = $" . $prefixLevel . "loop->length - (\$" . $prefixLevel . "incr + 1); ";
            $compilation .= "\$" . $prefixLevel . "loop->last = ($" . $prefixLevel . "incr == (\$" . $prefixLevel . "loop->length - 1)); ?>";
        }

        /**
         * Update the forelse var if it's iterated at least one time
         */
        if (is_string($forElse)) {
            $compilation .= "<?php \$" . $forElse . "iterated = true; ?>";
        }

        /**
         * Append the internal block compilation
         */
        $compilation .= $code;

        if (isset($statement["if_expr"])) {
            $compilation .= "<?php } ?>";
        }

        if (is_string($forElse)) {
            $compilation .= "<?php } ?>";
        } else {
            if (isset($loopContext[$level])) {
                $compilation .= "<?php \$" . $prefixLevel . "incr++; } ?>";
            } else {
                $compilation .= "<?php } ?>";
            }
        }

        $this->foreachLevel--;

        return $compilation;
    }

    /**
     * Generates a 'forelse' PHP code
     */
    public function compileForElse() : string
    {

        $level = $this->foreachLevel;
        $prefix = $this->forElsePointers[$level] ?? null;
        if ($prefix===null) {
            return "";
        }

        if (isset($this->loopPointers[$level])) {
            return "<?php \$" . $prefix . "incr++; } if (!\$" . $prefix . "iterated) { ?>";
        }

        return "<?php } if (!\$" . $prefix . "iterated) { ?>";
    }

    /**
     * Compiles a 'if' statement returning PHP code
     */
    public function compileIf(array $statement, bool $extendsMode = false) : string
    {

        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if ($expr === null) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * Process statements in the "true" block
         */
        $compilation = "<?php if (" . $this->expression($expr) . ") { ?>" . $this->statementList($statement["true_statements"], $extendsMode);

        /**
         * Check for a "else"/"elseif" block
         */
        $blockStatements = $statement["false_statements"] ?? null;
        if ($blockStatements !== null) {
            /**
             * Process statements in the "false" block
             */
            $compilation .= "<?php } else { ?>" . $this->statementList($blockStatements, $extendsMode);
        }

        $compilation .= "<?php } ?>";

        return $compilation;
    }

    /**
     * Compiles a 'include' statement returning PHP code
     */
    public function compileInclude(array $statement) : string
    {
        /**
         * Include statement
         * A valid expression is required
         */
        $pathExpr = $statement["path"] ?? null;
        if ($pathExpr === null) {
            throw new Exception("Corrupted statement");
        }

        /**
         * Check if the expression is a string
         * If the path is an string try to make an static compilation
         */
        if ($pathExpr["type"] === 260) {
            /**
             * Static compilation cannot be performed if the user passed extra
             * parameters
             */
            if (!isset($statement["params"])) {
                /**
                 * Get the static path
                 */
                $path = $pathExpr["value"];

                $finalPath = $this->getFinalPath($path);

                /**
                 * Clone the original compiler
                 * Perform a sub-compilation of the included file
                 * If the compilation doesn't return anything we include the compiled path
                 */
                $subCompiler = clone $this;
                $compilation = $subCompiler->compile($finalPath, false);

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
        }

        /**
         * Resolve the path's expression
         */
        $path = $this->expression($pathExpr);

        /**
         * Use partial
         */
        $params = $statement["params"] ?? null;
        if ($params === null) {
            return "<?php $this->partial(" . $path . "); ?>";
        }

        return "<?php $this->partial(" . $path . ", " . $this->expression($params) . "); ?>";
    }

    /**
     * Compiles macros
     */
    public function compileMacro(array $statement, bool $extendsMode) : string
    {
        /**
         * A valid name is required
         */
        $name = $statement["name"] ?? null;
        if (empty($name)) {
            throw new Exception("Corrupted statement");
        }

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

        $macroName = "$this->macros['" . $name . "']";

        $code = "<?php ";
		$parameters = $statement["parameters"] ?? null;
        if ($parameters === null) {
            $code .= $macroName . " = function() { ?>";
        } else {
            /**
             * Parameters are always received as an array
             */
            $code .= $macroName . " = function(\$__p = null) { ";

            foreach ($parameters as $position => $parameter) {
                $variableName = $parameter["variable"];

                $code .= "if (isset(\$__p[" . position . "])) { ";
                $code .= "\$" . $variableName . " = \$__p[" . position ."];";
                $code .= " } else { ";
                $code .= "if (array_key_exists(\"" . $variableName . "\", \$__p)) { ";
                $code .= "\$" . $variableName . " = \$__p[\"" . $variableName ."\"];";
                $code .= " } else { ";
                $defaultValue = $parameter["default"] ?? null;
                if ($defaultValue !== null) {
                    $code .= "\$" . $variableName . " = " . $this->expression($defaultValue) . ";";
                } else {
                    $code .= " throw new \\Phalcon\\Mvc\\View\\Exception(\"Macro '" . $name . "' was called without parameter: " . $variableName . "\"); ";
                }

                $code .= " } } ";
            }

            $code .= " ?>";
        }

        /**
         * Block statements are allowed
         */
        $blockStatements = $statement["block_statements"] ?? null;
        if ($blockStatements !== null) {
            /**
             * Process statements block
             */
            $code .= $this->statementList($blockStatements, $extendsMode) . "<?php }; ";
        }  else {
            $code .= "<?php }; ";
        }

        /**
         * Bind the closure to the $this object allowing to call services
         */
        $code .= $macroName . " = \\Closure::bind(" . $macroName . ", $this); ?>";

        return $code;
    }

    /**
     * Compiles a "return" statement returning PHP code
     */
    public function compileReturn(array $statement) : string
    {
        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if (empty($expr)) {
            throw new Exception("Corrupted statement");
        }

        /**
         * "Return" statement
         */
        return "<?php return " . $this->expression($expr) . "; ?>";
    }

    /**
     * Compiles a "set" statement returning PHP code
     */
    public function compileSet(array $statement) : string
    {

        /**
         * A valid assignment list is required
         */
        $assignments = $statement["assignments"] ?? null;
        if (empty($assignments)) {
            throw new Exception("Corrupted statement");
        }

        $compilation = "<?php";

        /**
         * A single set can have several assignments
         */
        foreach ($assignments as $assignment) {
            $exprCode = $this->expression(
                $assignment["expr"]
            );

            /**
             * Resolve the expression assigned
             */
            $target = $this->expression(
                $assignment["variable"]
            );

            /**
             * Assignment operator
             * Generate the right operator
             */
            switch ($assignment["op"]) {

                case PHVOLT_T_ADD_ASSIGN:
                    $compilation .= " " . $target . " += " . $exprCode . ";";
                    break;

                case PHVOLT_T_SUB_ASSIGN:
                    $compilation .= " " . $target . " -= " . $exprCode . ";";
                    break;

                case PHVOLT_T_MUL_ASSIGN:
                    $compilation .= " " . $target . " *= " . $exprCode . ";";
                    break;

                case PHVOLT_T_DIV_ASSIGN:
                    $compilation .= " " . $target . " /= " . $exprCode . ";";
                    break;

                default:
                    $compilation .= " " . $target . " = " . $exprCode . ";";
                    break;
            }

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
     */
    public function compileString(string $viewCode, bool $extendsMode = false) : string
    {
        $this->currentPath = "eval code";

        return $this->compileSource($viewCode, $extendsMode);
    }

    /**
     * Compiles a 'switch' statement returning PHP code
     */
    public function compileSwitch(array $statement, bool $extendsMode = false) : string
    {

        /**
         * A valid expression is required
         */
        $expr = $statement["expr"] ?? null;
        if (empty($expr)) {
            throw new Exception("Corrupt statement", $statement);
        }

        /**
         * Process statements in the "true" block
         */
        $compilation = "<?php switch (" . $this->expression($expr) . "): ?>";

        /**
         * Check for a "case"/"default" blocks
         */
        $caseClauses = $statement["case_clauses"] ?? null;
        if (!empty($caseClauses)) {
            $lines = $this->statementList($caseClauses, $extendsMode);

            /**
             * Any output (including whitespace) between a switch statement and
             * the first case will result in a syntax error. This is the
             * responsibility of the user. However, we can clear empty lines and
             * whitespace here to reduce the number of errors.
             *
             * http://php.net/control-structures.alternative-syntax
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
     */
    final public function expression(array $expr) : string
    {
        $exprCode = null;
        $this->exprLevel++;

        /**
         * Check if any of the registered extensions provide compilation for
         * this expression
         */
        $extensions = $this->extensions;

        while(true) {
            if (is_array($extensions)) {
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
            $type = $expr["type"] ?? null;
            if (empty($type)){
                $items = [];

                foreach ($expr as $singleExpr) {
                    $singleExprCode = $this->expression(
                        $singleExpr["expr"]
                    );
                    $name = $singleExpr["name"] ?? null;
                    if (!empty($name)) {
                        $items[] = "'" . $name . "' => " . $singleExprCode;
                    } else {
                        $items[] = $singleExprCode;
                    }
                }

                $exprCode = implode(", ", $items);

                break;
            }

            /**
             * Attribute reading needs special handling
             */
            if ($type === PHVOLT_T_DOT) {
                $exprCode = $this->attributeReader($expr);

                break;
            }

            /**
             * Left part of expression is always resolved
             */
            $left = $expr["left"] ?? null;
            if (!empty($left)) {
                $leftCode = $this->expression($left);
            }

            /**
             * Operator "is" also needs special handling
             */
            if ($type === PHVOLT_T_IS) {
                $exprCode = $this->resolveTest(
                    $expr["right"],
                    $leftCode
                );

                break;
            }

            /**
             * We don't resolve the right expression for filters
             */
            if ($type === 124) {
                $exprCode = $this->resolveFilter(
                    $expr["right"],
                    $leftCode
                );

                break;
            }

            /**
             * From here, right part of expression is always resolved
             */
            $right = $expr["right"] ?? null;
            if (!empty($right)) {
                $rightCode = $this->expression($right);
            }

            $exprCode = null;

            switch ($type) {
                case PHVOLT_T_NOT:
                    $exprCode = "!" . $rightCode;
                    break;

                case PHVOLT_T_MUL:
                    $exprCode = $leftCode . " * " . $rightCode;
                    break;

                case PHVOLT_T_ADD:
                    $exprCode = $leftCode . " + " . $rightCode;
                    break;

                case PHVOLT_T_SUB:
                    $exprCode = $leftCode . " - " . $rightCode;
                    break;

                case PHVOLT_T_DIV:
                    $exprCode = $leftCode . " / " . $rightCode;
                    break;

                case 37:
                    $exprCode = $leftCode . " % " . $rightCode;
                    break;

                case PHVOLT_T_LESS:
                    $exprCode = $leftCode . " < " . $rightCode;
                    break;

                case 61:
                    $exprCode = $leftCode . " > " . $rightCode;
                    break;

                case 62:
                    $exprCode = $leftCode . " > " . $rightCode;
                    break;

                case 126:
                    $exprCode = $leftCode . " . " . $rightCode;
                    break;

                case 278:
                    $exprCode = "pow(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case PHVOLT_T_ARRAY:
                    if (isset($expr["left"])){
                        $exprCode = "[" . $leftCode . "]";
                    } else {
                        $exprCode = "[]";
                    }

                    break;

                case 258:
                    $exprCode = $expr["value"];
                    break;

                case 259:
                    $exprCode = $expr["value"];
                    break;

                case PHVOLT_T_STRING:
                    $exprCode = "'" . str_replace("'", "\\'", $expr["value"]) . "'";
                    break;

                case PHVOLT_T_NULL:
                    $exprCode = "null";
                    break;

                case PHVOLT_T_FALSE:
                    $exprCode = "false";
                    break;

                case PHVOLT_T_TRUE:
                    $exprCode = "true";
                    break;

                case PHVOLT_T_IDENTIFIER:
                    $exprCode = "$" . $expr["value"];
                    break;

                case PHVOLT_T_AND:
                    $exprCode = $leftCode . " && " . $rightCode;
                    break;

                case 267:
                    $exprCode = $leftCode . " || " . $rightCode;
                    break;

                case PHVOLT_T_LESSEQUAL:
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

                case PHVOLT_T_RANGE:
                    $exprCode = "range(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case PHVOLT_T_FCALL:
                    $exprCode = $this->functionCall($expr);
                    break;

                case PHVOLT_T_ENCLOSED:
                    $exprCode = "(" . $leftCode . ")";
                    break;

                case PHVOLT_T_ARRAYACCESS:
                    $exprCode = $leftCode . "[" . $rightCode . "]";
                    break;

                case PHVOLT_T_SLICE:

                    /**
                     * Evaluate the start part of the slice
                     */
                    $start = $expr["start"] ?? null;
                    if (!empty($start)) {
                        $startCode = $this->expression($start);
                    } else {
                        $startCode = "null";
                    }

                    /**
                     * Evaluate the end part of the slice
                     */
                    $end = $expr["end"] ?? null;
                    if (!empty($end)) {
                        $endCode = $this->expression($end);
                    } else {
                        $endCode = "null";
                    }

                    $exprCode = "\$this->slice(" . $leftCode . ", " . $startCode . ", " . $endCode . ")";
                    break;

                case PHVOLT_T_NOT_ISSET:
                    $exprCode = "!isset(" . $leftCode . ")";
                    break;

                case PHVOLT_T_ISSET:
                    $exprCode = "isset(" . $leftCode . ")";
                    break;

                case PHVOLT_T_NOT_ISEMPTY:
                    $exprCode = "!empty(" . $leftCode . ")";
                    break;

                case PHVOLT_T_ISEMPTY:
                    $exprCode = "empty(" . $leftCode . ")";
                    break;

                case PHVOLT_T_NOT_ISEVEN:
                    $exprCode = "!(((" . $leftCode . ") % 2) == 0)";
                    break;

                case PHVOLT_T_ISEVEN:
                    $exprCode = "(((" . $leftCode . ") % 2) == 0)";
                    break;

                case PHVOLT_T_NOT_ISODD:
                    $exprCode = "!(((" . $leftCode . ") % 2) != 0)";
                    break;

                case PHVOLT_T_ISODD:
                    $exprCode = "(((" . $leftCode . ") % 2) != 0)";
                    break;

                case PHVOLT_T_NOT_ISNUMERIC:
                    $exprCode = "!is_numeric(" . $leftCode . ")";
                    break;

                case PHVOLT_T_ISNUMERIC:
                    $exprCode = "is_numeric(" . $leftCode . ")";
                    break;

                case PHVOLT_T_NOT_ISSCALAR:
                    $exprCode = "!is_scalar(" . $leftCode . ")";
                    break;

                case PHVOLT_T_ISSCALAR:
                    $exprCode = "is_scalar(" . $leftCode . ")";
                    break;

                case PHVOLT_T_NOT_ISITERABLE:
                    $exprCode = "!(is_array(" . $leftCode . ") || (" . $leftCode . ") instanceof Traversable)";
                    break;

                case PHVOLT_T_ISITERABLE:
                    $exprCode = "(is_array(" . $leftCode . ") || (" . $leftCode . ") instanceof Traversable)";
                    break;

                case PHVOLT_T_IN:
                    $exprCode = "\$this->isIncluded(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case PHVOLT_T_NOT_IN:
                    $exprCode = "!\$this->isIncluded(" . $leftCode . ", " . $rightCode . ")";
                    break;

                case PHVOLT_T_TERNARY:
                    $exprCode = "(" . $this->expression($expr["ternary"]) . " ? " . $leftCode . " : " . $rightCode . ")";
                    break;

                case PHVOLT_T_MINUS:
                    $exprCode = "-" . $rightCode;
                    break;

                case PHVOLT_T_PLUS:
                    $exprCode = "+" . $rightCode;
                    break;

                case PHVOLT_T_RESOLVED_EXPR:
                    $exprCode = $expr["value"];
                    break;

                default:
                    throw new Exception(
                        "Unknown expression " . $type . " in " . $expr["file"] . " on line " . $expr["line"]
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
     * @param array arguments
     * @return mixed
     */
    final public function fireExtensionEvent(string $name, $arguments = null)
    {
        $extensions = $this->extensions;

        if (is_array($extensions)) {
            foreach ($extensions as $extension) {
                /**
                 * Check if the extension implements the required event name
                 */
                if (method_exists($extension, $name)) {
                    if (is_array($arguments)) {
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
        }
    }

    /**
     * Resolves function intermediate code into PHP function calls
     */
    public function functionCall(array $expr) : string
    {
        $code          = null;
        $funcArguments = null;
        $funcArguments = $expr["arguments"] ?? null;
        if (!empty($funcArguments)) {
            $arguments = $this->expression($funcArguments);
        } else {
            $arguments = "";
        }

        $nameExpr = $expr["name"];
        $nameType = $nameExpr["type"];

        /**
         * Check if it's a single function
         */
        if ($nameType === PHVOLT_T_IDENTIFIER) {
            $name = $nameExpr["value"];

            /**
             * Check if any of the registered extensions provide compilation for
             * this function
             */
            $extensions = $this->extensions;

            if (is_array($extensions)) {
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
            $functions = $this->functions;

            if (is_array($functions)) {
            	$definition = $functions[$name] ?? null;
                if ($definition !== null) {
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
                    if (is_object($definition)) {
                        if ($definition instanceof Closure) {
                            return call_user_func_array(
                                $definition,
                                [$arguments, $funcArguments]
                            );
                        }
                    }

                    throw new Exception(
                        "Invalid definition for user function '" . $name . "' in " . $expr["file"] . " on line " . $expr["line"]
                    );
                }
            }

            /**
             * This function includes the previous rendering stage
             */
            if (($name === "get_content") || ($name === "content")) {
                return "\$this->getContent()";
            }

            /**
             * This function includes views of volt or others template engines
             * dynamically
             */
            if ($name === "partial") {
                return "\$this->partial(" . $arguments . ")";
            }

            /**
             * This function embeds the parent block in the current block
             */
            if ($name === "super") {
                $extendedBlocks = $this->extendedBlocks;

                if (is_array($extendedBlocks)) {
                    $currentBlock = $this->currentBlock;
                    $block = $extendedBlocks[$currentBlock] ?? null;
                    if ($block !== null) {
                        $exprLevel = $this->exprLevel;

                        if (is_array($block)) {
                            $code = $this->statementListOrExtends($block);

                            if ($exprLevel === 1) {
                                $escapedCode = $code;
                            } else {
                                $escapedCode = addslashes($code);
                            }
                        } else {
                            if ($exprLevel === 1) {
                                $escapedCode = $block;
                            } else {
                                $escapedCode = addslashes($block);
                            }
                        }

                        /**
                         * If the super() is the first level we don't escape it
                         */
                        if ($exprLevel === 1) {
                            return $escapedCode;
                        }

                        return "'" . $escapedCode . "'";
                    }
                }
                return "''";
            }

            $method = lcfirst(
                camelize($name)
            );

            $arrayHelpers = [
                "link_to" =>        true,
                "image" =>          true,
                "form" =>           true,
                "submit_button" =>  true,
                "radio_field" =>    true,
                "check_field" =>    true,
                "file_field" =>     true,
                "hidden_field" =>   true,
                "password_field" => true,
                "text_area" =>      true,
                "text_field" =>     true,
                "email_field" =>    true,
                "date_field" =>     true,
                "tel_field" =>      true,
                "numeric_field" =>  true,
                "image_input" =>    true
            ];

            /**
             * Check if it's a method in Phalcon\Tag
             */
            if (method_exists("Phalcon\\Tag", $method)) {
                if (isset($arrayHelpers[$name])){
                    return "\$this->tag->" . $method . "([" . $arguments . "])";
                }

                return "\$this->tag->" . $method . "(" . $arguments . ")";
            }

            /**
             * The code below will be activated when Html\Tag is enabled
             */
            /**
            arrayHelpers = [
                "button_submit"         : true.
                "element"               : true.
                "element_close"         : true.
                "end_form"              : true.
                "form"                  : true.
                "friendly_title"        : true.
                "get_doc_type"          : true.
                "get_title"             : true.
                "get_title_separator"   : true.
                "image"                 : true.
                "input_checkbox"        : true.
                "input_color"           : true.
                "input_date"            : true.
                "input_date_time"       : true.
                "input_date_time_local" : true.
                "input_email"           : true.
                "input_file"            : true.
                "input_hidden"          : true.
                "input_image"           : true.
                "input_month"           : true.
                "input_numeric"         : true.
                "input_password"        : true.
                "input_radio"           : true.
                "input_range"           : true.
                "input_search"          : true.
                "input_tel"             : true.
                "input_text"            : true.
                "input_time"            : true.
                "input_url"             : true.
                "input_week"            : true.
                "javascript"            : true.
                "link"                  : true.
                "prepend_title"         : true.
                "render_title"          : true.
                "select"                : true.
                "stylesheet"            : true.
                "submit"                : true.
                "text_area"             : true.
            ];

            if (method_exists("Phalcon\\Html\\Tag", method)) {
                if isset(arrayHelpers[name]){
                    return "$this->tag->" . method . "([" . arguments . "])";
                }

                return "$this->tag->" . method . "(" . arguments . ")";
            }
            */

            /**
             * Get a dynamic URL
             */
            if ($name === "url") {
                return "\$this->url->get(" . $arguments . ")";
            }

            /**
             * Get a static URL
             */
            if ($name === "static_url") {
                return "\$this->url->getStatic(" . $arguments . ")";
            }

            if ($name === "date") {
                return "date(" . $arguments . ")";
            }

            if ($name === "time") {
                return "time()";
            }

            if ($name === "dump") {
                return "var_dump(" . $arguments . ")";
            }

            if ($name === "version") {
                return "Phalcon\\Version::get()";
            }

            if ($name === "version_id") {
                return "Phalcon\\Version::getId()";
            }

            if ($name === "preload") {
                return "\$this->tag->preload(" . $arguments . ")";
            }

            /**
             * Read PHP constants in templates
             */
            if ($name === "constant") {
                return "constant(" . $arguments . ")";
            }

            /**
             * By default it tries to call a macro
             */
            return "\$this->callMacro('" . $name . "', [" . $arguments . "])";
        }

        return $this->expression($nameExpr) . "(" . $arguments . ")";
    }

    /**
     * Returns the path to the last compiled template
     */
    public function getCompiledTemplatePath() : string
    {
        return $this->compiledTemplatePath;
    }

    /**
     * Returns the internal dependency injector
     */
    public function getDI() : DiInterface
    {
        return $this->container;
    }

    /**
     * Returns the list of extensions registered in Volt
     */
    public function getExtensions() : array
    {
        return $this->extensions;
    }

    /**
     * Register the user registered filters
     */
    public function getFilters() : array
    {
        return $this->filters;
    }

    /**
     * Register the user registered functions
     */
    public function getFunctions() : array
    {
        return $this->functions;
    }

    /**
     * Returns a compiler's option
     *
     * @return string
     */
    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }

    /**
     * Returns the compiler options
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Returns the path that is currently being compiled
     */
    public function getTemplatePath() : string
    {
        return $this->currentPath;
    }

    /**
     * Return a unique prefix to be used as prefix for compiled variables and
     * contexts
     */
    public function getUniquePrefix() : string
    {
        /**
         * If the unique prefix is not set we use a hash using the modified
         * Berstein algorithm
         */
        if (!$this->prefix) {
            $this->prefix = unique_path_key($this->currentPath);
        }

        /**
         * The user could use a closure generator
         */
        if (is_object($this->prefix)) {
            if ($this->prefix instanceof Closure) {
                $this->prefix = call_user_func_array(
                    $this->prefix,
                    [
                        $this
                    ]
                );
            }
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
     * @return array
     */
    public function parse(string $viewCode)
    {
        $currentPath = "eval code";

        return phvolt_parse_view($viewCode, $currentPath);
    }

    /**
     * Resolves filter intermediate code into a valid PHP expression
     */
    public function resolveTest(array $test, string $left) : string
    {
        $type = $test["type"];

        /**
         * Check if right part is a single identifier
         */
        if ($type === PHVOLT_T_IDENTIFIER) {
            $name = $test["value"];

            /**
             * Empty uses the PHP's empty operator
             */
            if ($name === "empty") {
                return "empty(" . $left . ")";
            }

            /**
             * Check if a value is even
             */
            if ($name === "even") {
                return "(((" . $left . ") % 2) == 0)";
            }

            /**
             * Check if a value is odd
             */
            if ($name === "odd") {
                return "(((" . $left . ") % 2) != 0)";
            }

            /**
             * Check if a value is numeric
             */
            if ($name === "numeric") {
                return "is_numeric(" . $left . ")";
            }

            /**
             * Check if a value is scalar
             */
            if ($name === "scalar") {
                return "is_scalar(" . $left . ")";
            }

            /**
             * Check if a value is iterable
             */
            if ($name === "iterable") {
                return "(is_array(" . $left . ") || (" . $left . ") instanceof Traversable)";
            }

        }

        /**
         * Check if right part is a function call
         */
        if ($type === PHVOLT_T_FCALL) {
            $testName = $test["name"];
            $name = $testName["value"] ?? null;

            if ($name) {
                if ($name === "divisibleby") {
                    return "(((" . $left . ") % (" . $this->expression($test["arguments"]) . ")) == 0)";
                }

                /**
                 * Checks if a value is equals to other
                 */
                if ($name === "sameas") {
                    return "(" . $left . ") === (" . $this->expression($test["arguments"]) . ")";
                }

                /**
                 * Checks if a variable match a type
                 */
                if ($name === "type") {
                    return "gettype(" . $left . ") === (" . $this->expression($test["arguments"]) . ")";
                }
            }
        }

        /**
         * Fall back to the equals operator
         */
        return $left . " == " . $this->expression($test);
    }

    /**
     * Sets the dependency injector
     */
    public function setDI(DiInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * Sets a single compiler option
     *
     * @param mixed value
     */
    public function setOption(string $option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Sets the compiler options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Set a unique prefix to be used as prefix for compiled variables
     */
    public function setUniquePrefix(string $prefix) : Compiler
    {
        $this->prefix = $prefix;

        return $this;
    }


    /**
     * Compiles a Volt source code returning a PHP plain version
     */
    protected function compileSource(string $viewCode, bool $extendsMode = false) : string
    {
        $currentPath = $this->currentPath;

        /**
         * Check for compilation options
         */
        $options = $this->options;

        if (is_array($options)) {
            /**
             * Enable autoescape globally
             */
            $autoescape = $options["autoescape"] ?? null;
            if ($autoescape) {
                if (!is_bool($autoescape)) {
                    throw new Exception("'autoescape' must be bool");
                }

                $this->autoescape = $autoescape;
            }
        }

        $intermediate = phvolt_parse_view($viewCode, $currentPath);

        /**
         * The parsing must return a valid array
         */
        if (!is_array($intermediate)) {
            throw new Exception("Invalid intermediate representation");
        }

        $compilation = $this->statementList($intermediate, $extendsMode);

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

            $blocks = $this->blocks;
            $extendedBlocks = $this->extendedBlocks;

            foreach ($extendedBlocks as $name => $block) {
                /**
                 * If name is a string then is a block name
                 */
                if (is_string($name)) {
                    if (isset($blocks[$name])) {
                        /**
                         * The block is set in the local template
                         */
                        $localBlock = $blocks[$name];
                        $this->currentBlock = $name;
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
     */
    protected function getFinalPath(string $path)
    {

        $view = $this->view;

        if (is_object($view)) {
            $viewsDirs = $view->getViewsDir();

            if (is_array($viewsDirs)) {
                foreach ($viewsDirs as $viewsDir) {
                    if (file_exists($viewsDir . $path)) {
                        return $viewsDir . $path;
                    }
                }

                // Otherwise, take the last viewsDir
                return $viewsDir . $path;
            } else {
                return $viewsDirs . $path;
            }
        }

        return $path;
    }

    /**
     * Resolves filter intermediate code into PHP function calls
     */
    final protected function resolveFilter(array $filter, string $left) : string
    {
        $code = null;
        $type = $filter["type"];

        /**
         * Check if the filter is a single identifier
         */
        if ($type == PHVOLT_T_IDENTIFIER) {
            $name = $filter["value"];
        } else {
            if ($type !== PHVOLT_T_FCALL) {
                /**
                 * Unknown filter throw an exception
                 */
                throw new Exception(
                    "Unknown filter type in " . $filter["file"] . " on line " . $filter["line"]
                );
            }

            $functionName = $filter["name"];
            $name = $functionName["value"];
        }

        $funcArguments = null;
        $arguments = null;

        /**
         * Resolve arguments
         */
        $funcArguments = $filter["arguments"] ?? null;
        if ($funcArguments) {
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
                            "type" =>  364,
                            "value" => $left,
                            "file" =>  $file,
                            "line" =>  $line
                        ],
                        "file" => $file,
                        "line" => $line
                    ]
                );
            }

            $arguments = $this->expression($funcArguments);
        } else {
            $arguments = $left;
        }

        /**
         * Check if any of the registered extensions provide compilation for
         * this filter
         */
        $extensions = $this->extensions;

        if (is_array($extensions)) {
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
        $filters = $this->filters;
        $definition = $filters[$name] ?? null;
        if ($definition) {
            /**
             * The definition is a string
             */
            if (is_string($definition)) {
                return $definition . "(" . $arguments . ")";
            }

            /**
             * The definition is a closure
             */
            if (is_object($definition)) {
                if ($definition instanceof Closure) {
                    return call_user_func_array(
                        $definition,
                        [$arguments, $funcArguments]
                    );
                }
            }

            /**
             * Invalid filter definition throw an exception
             */
            throw new Exception(
                "Invalid definition for user filter '" . $name . "' in " . $filter["file"] . " on line " . $filter["line"]
            );
        }

        /**
         * "length" uses the length method implemented in the Volt adapter
         */
        if ($name === "length") {
            return "$this->length(" . $arguments . ")";
        }

        /**
         * "e"/"escape" filter uses the escaper component
         */
        if (($name === "e") || ($name === "escape")) {
            return "\$this->escaper->escapeHtml(" . $arguments . ")";
        }

        /**
         * "escape_css" filter uses the escaper component to filter CSS
         */
        if ($name === "escape_css") {
            return "$this->escaper->escapeCss(" . $arguments . ")";
        }

        /**
         * "escape_js" filter uses the escaper component to escape JavaScript
         */
        if ($name === "escape_js") {
            return "$this->escaper->escapeJs(" . $arguments . ")";
        }

        /**
         * "escape_attr" filter uses the escaper component to escape HTML
         * attributes
         */
        if ($name === "escape_attr") {
            return "$this->escaper->escapeHtmlAttr(" . $arguments . ")";
        }

        /**
         * "trim" calls the "trim" function in the PHP userland
         */
        if ($name === "trim") {
            return "trim(" . $arguments . ")";
        }

        /**
         * "left_trim" calls the "ltrim" function in the PHP userland
         */
        if ($name === "left_trim") {
            return "ltrim(" . $arguments . ")";
        }

        /**
         * "right_trim" calls the "rtrim" function in the PHP userland
         */
        if ($name === "right_trim") {
            return "rtrim(" . $arguments . ")";
        }

        /**
         * "striptags" calls the "strip_tags" function in the PHP userland
         */
        if ($name === "striptags") {
            return "strip_tags(" . $arguments . ")";
        }

        /**
         * "url_encode" calls the "urlencode" function in the PHP userland
         */
        if ($name === "url_encode") {
            return "urlencode(" . $arguments . ")";
        }

        /**
         * "slashes" calls the "addslashes" function in the PHP userland
         */
        if ($name === "slashes") {
            return "addslashes(" . $arguments . ")";
        }

        /**
         * "stripslashes" calls the "stripslashes" function in the PHP userland
         */
        if ($name === "stripslashes") {
            return "stripslashes(" . $arguments . ")";
        }

        /**
         * "nl2br" calls the "nl2br" function in the PHP userland
         */
        if ($name === "nl2br") {
            return "nl2br(" . $arguments . ")";
        }

        /**
         * "keys" uses calls the "array_keys" function in the PHP userland
         */
        if ($name === "keys") {
            return "array_keys(" . $arguments . ")";
        }

        /**
         * "join" uses calls the "join" function in the PHP userland
         */
        if ($name === "join") {
            return "join('" . $funcArguments[1]["expr"]["value"] . "', " . $funcArguments[0]["expr"]["value"] . ")";
        }

        /**
         * "lower"/"lowercase" calls the "strtolower" function or
         * "mb_strtolower" if the mbstring extension is loaded
         */
        if (($name === "lower") || ($name === "lowercase")) {
            return "Phalcon\\Text::lower(" . $arguments . ")";
        }

        /**
         * "upper"/"uppercase" calls the "strtoupper" function or
         * "mb_strtoupper" if the mbstring extension is loaded
         */
        if (($name === "upper") || ($name === "uppercase")) {
            return "Phalcon\\Text::upper(" . $arguments . ")";
        }

        /**
         * "capitalize" filter calls "ucwords"
         */
        if ($name === "capitalize") {
            return "ucwords(" . $arguments . ")";
        }

        /**
         * "sort" calls "sort" method in the engine adapter
         */
        if ($name === "sort") {
            return "$this->sort(" . $arguments . ")";
        }

        /**
         * "json_encode" calls the "json_encode" function in the PHP userland
         */
        if ($name === "json_encode") {
            return "json_encode(" . $arguments . ")";
        }

        /**
         * "json_decode" calls the "json_decode" function in the PHP userland
         */
        if ($name === "json_decode") {
            return "json_decode(" . $arguments . ")";
        }

        /**
         * "format" calls the "sprintf" function in the PHP userland
         */
        if ($name === "format") {
            return "sprintf(" . $arguments . ")";
        }

        /**
         * "abs" calls the "abs" function in the PHP userland
         */
        if ($name === "abs") {
            return "abs(" . $arguments . ")";
        }

        /**
         * "slice" slices string/arrays/traversable objects
         */
        if ($name === "slice") {
            return "$this->slice(" . $arguments . ")";
        }

        /**
         * "default" checks if a variable is empty
         */
        if ($name === "default") {
            return "(empty(" . $left . ") ? (" . $arguments . ") : (" . $left . "))";
        }

        /**
         * This function uses mbstring or iconv to convert strings from one
         * charset to another
         */
        if ($name === "convert_encoding") {
            return "$this->convertEncoding(" . $arguments . ")";
        }

        /**
         * Unknown filter throw an exception
         */
        throw new Exception(
            "Unknown filter \"" . $name . "\" in " . $filter["file"] . " on line " . $filter["line"]
        );
    }

    /**
     * Traverses a statement list compiling each of its nodes
     */
    final protected function statementList(array $statements, bool $extendsMode = false) : string
    {
        /**
         * Nothing to compile
         */
        if (!count($statements)) {
            return "";
        }

        /**
         * Increase the statement recursion level in extends mode
         */
        $extended = $this->extended;
        $blockMode = $extended || $extendsMode;

        if ($blockMode === true) {
            $this->blockLevel++;
        }

        $this->level++;

        $compilation = null;

        $extensions = $this->extensions;

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
            if (!isset($statement["type"])){
                throw new Exception(
                    "Invalid statement in " . $statement["file"] . " on line " . $statement["line"],
                    $statement
                );
            }

            /**
             * Check if extensions have implemented custom compilation for this
             * statement
             */
            if (is_array($extensions)) {
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

                case PHVOLT_T_RAW_FRAGMENT:
                    $compilation .= $statement["value"];
                    break;

                case PHVOLT_T_IF:
                    $compilation .= $this->compileIf($statement, $extendsMode);
                    break;

                case PHVOLT_T_ELSEIF:
                    $compilation .= $this->compileElseIf($statement);
                    break;

                case PHVOLT_T_SWITCH:
                    $compilation .= $this->compileSwitch(
                        $statement,
                        $extendsMode
                    );

                    break;

                case PHVOLT_T_CASE:
                    $compilation .= $this->compileCase($statement);
                    break;

                case PHVOLT_T_DEFAULT:
                    $compilation .= $this->compileCase($statement, false);
                    break;

                case PHVOLT_T_FOR:
                    $compilation .= $this->compileForeach(
                        $statement,
                        $extendsMode
                    );

                    break;

                case PHVOLT_T_SET:
                    $compilation .= $this->compileSet($statement);
                    break;

                case PHVOLT_T_ECHO:
                    $compilation .= $this->compileEcho($statement);
                    break;

                case PHVOLT_T_BLOCK:

                    /**
                     * Block statement
                     */
                    $blockName = $statement["name"];

                    $blockStatements = $statement["block_statements"];

                    $blocks = $this->blocks;

                    if ($blockMode) {
                        if (!is_array($blocks)) {
                            $blocks = [];
                        }

                        /**
                         * Create a unamed block
                         */
                        if ($compilation !== null) {
                            $blocks[] = $compilation;
                            $compilation = null;
                        }

                        /**
                         * In extends mode we add the block statements to the
                         * blocks variable
                         */
                        $blocks[$blockName] = $blockStatements;
                        $this->blocks = $blocks;
                    } else {
                        if (is_array($blockStatements)) {
                            $compilation .= $this->statementList(
                                $blockStatements,
                                $extendsMode
                            );
                        }
                    }

                    break;

                case PHVOLT_T_EXTENDS:

                    /**
                     * Extends statement
                     */
                    $path = $statement["path"];

                    $finalPath = $this->getFinalPath(
                        $path["value"]
                    );

                    $extended = true;

                    /**
                     * Perform a sub-compilation of the extended file
                     */
                    $subCompiler = clone $this;

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

                    $this->extended = true;
                    $this->extendedBlocks = $tempCompilation;
                    $blockMode = $extended;

                    break;

                case PHVOLT_T_INCLUDE:
                    $compilation .= $this->compileInclude($statement);

                    break;

                case PHVOLT_T_DO:
                    $compilation .= $this->compileDo($statement);
                    break;

                case PHVOLT_T_RETURN:
                    $compilation .= $this->compileReturn($statement);
                    break;

                case PHVOLT_T_AUTOESCAPE:
                    $compilation .= $this->compileAutoEscape(
                        $statement,
                        $extendsMode
                    );

                    break;

                case PHVOLT_T_CONTINUE:
                    /**
                     * "Continue" statement
                     */
                    $compilation .= "<?php continue; ?>";
                    break;

                case PHVOLT_T_BREAK:
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

                case PHVOLT_T_MACRO:
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
                        "Unknown statement " . $type . " in " . $statement["file"] . " on line " . $statement["line"]
                    );

            }
        }

        /**
         * Reduce the statement level nesting
         */
        if ($blockMode === true) {
            $level = $this->blockLevel;

            if ($level === 1) {
                if ($compilation !== null) {
                    $this->blocks[] = $compilation;
                }
            }

            $this->blockLevel--;
        }

        $this->level--;

        return $compilation;
    }

    /**
     * Compiles a block of statements
     *
     * @param array statements
     * @return string|array
     */
    final protected function statementListOrExtends(array $statements)
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

        if (!isset($statements["type"])){
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
}
