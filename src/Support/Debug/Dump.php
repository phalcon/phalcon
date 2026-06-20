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

use InvalidArgumentException;
use JsonException;
use Phalcon\Container\Container;
use Phalcon\Contracts\Support\Debug\TemplateAware;
use Phalcon\Di\DiInterface;
use Phalcon\Support\Debug\Traits\TemplateAwareTrait;
use Phalcon\Support\Helper\Json\Encode;
use Phalcon\Traits\Helper\Str\InterpolateTrait;
use Reflection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

use function array_merge;
use function call_user_func_array;
use function func_get_args;
use function get_class;
use function get_class_methods;
use function get_object_vars;
use function get_parent_class;
use function htmlentities;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function mb_strlen;
use function nl2br;
use function str_repeat;

use const ENT_IGNORE;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_EOL;

/**
 * Dumps information about a variable(s)
 *
 * ```php
 * $foo = 123;
 *
 * echo (new \Phalcon\Debug\Dump())->variable($foo, "foo");
 * ```
 *
 * ```php
 * $foo = "string";
 * $bar = ["key" => "value"];
 * $baz = new stdClass();
 *
 * echo (new \Phalcon\Debug\Dump())->variables($foo, $bar, $baz);
 * ```
 *
 * @property bool  $detailed
 * @property array $methods
 * @property array $styles
 */
class Dump implements TemplateAware
{
    use InterpolateTrait;
    use TemplateAwareTrait;

    /**
     * @var bool
     */
    protected bool $detailed = false;

    /**
     * @var array
     */
    protected array $methods = [];

    /**
     * @var array
     */
    protected array $styles = [];

    /**
     * @var Encode
     */
    private Encode $encode;

    /**
     * Dump constructor.
     *
     * @param array $styles
     * @param bool  $detailed
     */
    public function __construct(array $styles = [], bool $detailed = false)
    {
        $this->encode = new Encode();

        $this->setStyles($styles);

        $this->detailed = $detailed;
    }

    /**
     * Alias of variables() method
     *
     * @return string
     */
    public function all(): string
    {
        return call_user_func_array(
            [
                $this,
                'variables',
            ],
            func_get_args()
        );
    }

    /**
     * @return bool
     */
    public function getDetailed(): bool
    {
        return $this->detailed;
    }

    /**
     * Alias of variable() method
     *
     * @param mixed       $variable
     * @param string|null $name
     *
     * @return string
     * @throws ReflectionException
     */
    public function one(mixed $variable, string | null $name = null): string
    {
        return $this->variable($variable, $name);
    }

    /**
     * @param bool $flag
     */
    public function setDetailed(bool $flag): void
    {
        $this->detailed = $flag;
    }

    /**
     * Set styles for vars type
     *
     * @param array $styles
     *
     * @return array
     */
    public function setStyles(array $styles = []): array
    {
        $defaultStyles = [
            'pre'   => 'background-color:#f3f3f3; font-size:11px; ' .
                'padding:10px; border:1px solid #ccc; ' .
                'text-align:left; color:#333',
            'arr'   => 'color:red',
            'bool'  => 'color:green',
            'float' => 'color:fuchsia',
            'int'   => 'color:blue',
            'null'  => 'color:black',
            'num'   => 'color:navy',
            'obj'   => 'color:purple',
            'other' => 'color:maroon',
            'res'   => 'color:lime',
            'str'   => 'color:teal',
        ];

        $this->styles = array_merge($defaultStyles, $styles);

        return $this->styles;
    }

    /**
     * Returns an JSON string of information about a single variable.
     *
     * ```php
     * $foo = [
     *     "key" => "value",
     * ];
     *
     * echo (new \Phalcon\Debug\Dump())->toJson($foo);
     *
     * $foo = new stdClass();
     * $foo->bar = "buz";
     *
     * echo (new \Phalcon\Debug\Dump())->toJson($foo);
     * ```
     *
     * @param mixed $variable
     *
     * @return string
     * @throws InvalidArgumentException if the JSON cannot be encoded.
     * @throws JsonException
     */
    public function toJson(mixed $variable): string
    {
        return $this->encode->__invoke(
            $variable,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Returns an HTML string of information about a single variable.
     *
     * ```php
     * echo (new \Phalcon\Debug\Dump())->variable($foo, "foo");
     * ```
     *
     * @param mixed       $variable
     * @param string|null $name
     *
     * @return string
     * @throws ReflectionException
     */
    public function variable(mixed $variable, string | null $name = null): string
    {
        $message = $this->getTemplate('pre');
        $context = [
            'style'  => $this->getStyle('pre'),
            'output' => $this->output($variable, $name),
        ];

        return $this->toInterpolate($message, $context);
    }

    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag.
     *
     * ```php
     * $foo = "string";
     * $bar = ["key" => "value"];
     * $baz = new stdClass();
     *
     * echo (new \Phalcon\Debug\Dump())->variables($foo, $bar, $baz);
     * ```
     *
     * @return string
     * @throws ReflectionException
     */
    public function variables(): string
    {
        $output = "";
        $args   = func_get_args();

        foreach ($args as $key => $value) {
            $output .= $this->one($value, 'var ' . $key);
        }

        return $output;
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
            'pre'                     => '<pre style="%style%">%output%</pre>',
            'bold'                    => '<b style="%style%">%text%</b>',
            'varParens'               => '(<span style="%style%">%var%</span>)',
            'lengthValue'             => '(<span style="%style%">%length%</span>) '
                . '"<span style="%style%">%var%</span>"',
            'arrayHeader'             => '<b style="%style%">Array</b> '
                . '(<span style="%style%">%count%</span>) (',
            'arrayKey'                => '[<span style="%style%">%key%</span>] => ',
            'objectHeader'            => '<b style="%style%">Object</b> %class%',
            'objectExtends'           => ' <b style="%style%">extends</b> {parent}',
            'objectProperty'          => '-><span style="%style%">%key%</span> '
                . '(<span style="%style%">%type%</span>) = ',
            'objectMethods'           => "%class% <b style=\"%style%\">methods</b>: "
                . "(<span style=\"%style%\">%count%</span>) (\n",
            'objectMethod'            => "-><span style=\"%style%\">:method</span>();\n",
            'objectMethodConstructor' => "-><span style=\"%style%\">:method</span>(); "
                . "[<b style=\"%style%\">constructor</b>]\n",
            default                   => '',
        };
    }

    /**
     * Get style for type
     *
     * @param string $type
     *
     * @return string
     */
    protected function getStyle(string $type): string
    {
        if (isset($this->styles[$type])) {
            return $this->styles[$type];
        }

        return 'color:gray';
    }

    /**
     * Prepare an HTML string of information about a single variable.
     *
     * @param mixed       $variable
     * @param string|null $name
     * @param int         $tab
     *
     * @return string
     * @throws ReflectionException
     */
    protected function output(
        mixed $variable,
        string | null $name = null,
        int $tab = 1
    ): string {
        $space  = '  ';
        $output = '';

        if (!empty($name)) {
            $output .= $name . ' ';
        }

        if (is_array($variable)) {
            $message = $this->getTemplate('arrayHeader') . PHP_EOL;
            $context = [
                'style' => $this->getStyle('arr'),
                'count' => count($variable),
            ];

            $output .= $this->toInterpolate($message, $context);
            foreach ($variable as $key => $value) {
                $output .= str_repeat($space, $tab);

                $message = $this->getTemplate('arrayKey');
                $context = [
                    'style' => $this->getStyle('arr'),
                    'key'   => $key,
                ];
                $output  .= $this->toInterpolate($message, $context);

                if (
                    1 === $tab &&
                    !empty($name) &&
                    true !== is_int($key) &&
                    $name === $key
                ) {
                    continue;
                }

                $output .= $this->output($value, '', $tab + 1) . "\n";
            }

            return $output . str_repeat($space, $tab - 1) . ')';
        }

        if (is_object($variable)) {
            $message = $this->getTemplate('objectHeader');
            $context = [
                'style' => $this->getStyle('obj'),
                'class' => get_class($variable),
            ];
            $output  .= $this->toInterpolate($message, $context);

            if (false !== get_parent_class($variable)) {
                $message = $this->getTemplate('objectExtends');
                $context = [
                    'style'  => $this->getStyle('obj'),
                    'parent' => get_parent_class($variable),
                ];
                $output  .= $this->toInterpolate($message, $context);
            }

            $output .= " (\n";

            if ($variable instanceof DiInterface || $variable instanceof Container) {
                // Skip debugging di and container
                $output .= str_repeat($space, $tab) . "[skipped]\n";
            } elseif (true !== $this->detailed || $variable instanceof stdClass) {
                // Debug only public properties
                $vars = get_object_vars($variable);
                foreach ($vars as $key => $value) {
                    $message = $this->getTemplate('objectProperty');
                    $context = [
                        'style' => $this->getStyle('obj'),
                        'key'   => $key,
                        'type'  => 'public',
                    ];

                    $output .= str_repeat($space, $tab)
                        . $this->toInterpolate($message, $context)
                        . $this->output($value, '', $tab + 1)
                        . "\n";
                }
            } else {
                // Debug all properties
                $reflect = new ReflectionClass($variable);
                $props   = $reflect->getProperties(
                    ReflectionProperty::IS_PUBLIC |
                    ReflectionProperty::IS_PROTECTED |
                    ReflectionProperty::IS_PRIVATE
                );

                foreach ($props as $property) {
                    $property->setAccessible(true);
                    $key  = $property->getName();
                    $type = implode(
                        ' ',
                        Reflection::getModifierNames($property->getModifiers())
                    );

                    $message = $this->getTemplate('objectProperty');
                    $context = [
                        'style' => $this->getStyle('obj'),
                        'key'   => $key,
                        'type'  => $type,
                    ];

                    $output .= str_repeat($space, $tab)
                        . $this->toInterpolate($message, $context)
                        . $this->output($property->getValue($variable), '', $tab + 1)
                        . "\n";
                }
            }

            $attr    = get_class_methods($variable);
            $message = $this->getTemplate('objectMethods');
            $context = [
                'style' => $this->getStyle('obj'),
                'class' => get_class($variable),
                'count' => count($attr),
            ];

            $output .= str_repeat($space, $tab)
                . $this->toInterpolate($message, $context);


            if (true === in_array(get_class($variable), $this->methods)) {
                $output .= str_repeat($space, $tab) . "[already listed]\n";
            } else {
                foreach ($attr as $value) {
                    $this->methods[] = get_class($variable);

                    $message = $this->getTemplate('objectMethod');
                    if ('__construct' === $value) {
                        $message = $this->getTemplate('objectMethodConstructor');
                    }
                    $context = [
                        'style'  => $this->getStyle('obj'),
                        'method' => $value,
                    ];

                    $output .= str_repeat($space, $tab + 1)
                        . $this->toInterpolate($message, $context);
                }

                $output .= str_repeat($space, $tab) . ")\n";
            }

            return $output . str_repeat($space, $tab - 1) . ")";
        }

        if (is_int($variable)) {
            $message = $this->getOutputBold('Integer') . ' ' . $this->getTemplate('varParens');
            $context = [
                'style' => $this->getStyle('int'),
                'var'   => $variable,
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        if (is_float($variable)) {
            $message = $this->getOutputBold('Float') . ' ' . $this->getTemplate('varParens');
            $context = [
                'style' => $this->getStyle('float'),
                'var'   => $variable,
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        if (is_numeric($variable)) {
            $message = $this->getOutputBold('Numeric String') . ' ' . $this->getTemplate('lengthValue');
            $context = [
                'style'  => $this->getStyle('num'),
                'length' => mb_strlen((string)$variable),
                'var'    => $variable,
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        if (is_string($variable)) {
            $message = $this->getOutputBold('String') . ' ' . $this->getTemplate('lengthValue');
            $context = [
                'style'  => $this->getStyle('str'),
                'length' => mb_strlen($variable),
                'var'    => nl2br(htmlentities($variable, ENT_IGNORE, 'utf-8')),
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        if (is_bool($variable)) {
            $message = $this->getOutputBold('Boolean') . ' ' . $this->getTemplate('varParens');
            $context = [
                'style' => $this->getStyle('bool'),
                'var'   => ($variable) ? 'TRUE' : 'FALSE',
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        if (null === $variable) {
            $message = $this->getOutputBold('NULL');
            $context = [
                'style' => $this->getStyle('null'),
            ];

            return $output . $this->toInterpolate($message, $context);
        }

        $message = $this->getTemplate('varParens');
        $context = [
            'style' => $this->getStyle('other'),
            'var'   => $variable,
        ];

        return $output . $this->toInterpolate($message, $context);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function getOutputBold(string $text): string
    {
        return $this->toInterpolate(
            $this->getTemplate('bold'),
            ['text' => $text]
        );
    }
}
