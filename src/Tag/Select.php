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

namespace Phalcon\Tag;

use Closure;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Tag as BaseTag;
use Stringable;

use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function str_contains;

/**
 * Phalcon\Tag\Select
 *
 * Generates a SELECT HTML tag using a static array of values or a
 * Phalcon\Mvc\Model resultset
 *
 * @phpstan-import-type tag_parameters from BaseTag
 * @phpstan-import-type tag_select_data from BaseTag
 */
abstract class Select
{
    protected const OPTION_CLOSE = '</option>';
    protected const SELECT_CLOSE = '</select>';

    /**
     * Generates a SELECT tag
     *
     * @phpstan-param tag_parameters|string $parameters
     *
     * @param array|string $parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'useEmpty' => false,
     *     'emptyValue' => '',
     *     'emptyText' => '',
     * ]
     *
     * @return string
     * @throws Exception
     */
    public static function selectField(
        array | string $parameters,
        mixed $data = null
    ): string {
        $emptyText  = '';
        $emptyValue = '';
        $params     = $parameters;
        if (is_string($params)) {
            $params = [$parameters, $data];
        }

        if (!isset($params[0])) {
            $params[0] = $params["id"] ?? "";
        }

        $id = self::toStringValue($params[0]);

        /**
         * Automatically assign the id if the name is not an array
         */
        if (!str_contains($id, "[") && !isset($params["id"])) {
            $params["id"] = $id;
        }

        if (!isset($params["name"])) {
            $params["name"] = $id;
        } else {
            $name = $params["name"];
            if (!$name) {
                $params["name"] = $id;
            }
        }

        $value = $params["value"] ?? BaseTag::getValue($id, $params);
        if (isset($params["value"])) {
            unset($params["value"]);
        }

        $useEmpty = $params["useEmpty"] ?? false;
        if (isset($params["useEmpty"])) {
            $emptyValue = $params["emptyValue"] ?? "";
            if (isset($params["emptyValue"])) {
                unset($params["emptyValue"]);
            }

            $emptyText = $params["emptyText"] ?? "Choose...";
            if (isset($params["emptyText"])) {
                unset($params["emptyText"]);
            }

            unset($params["useEmpty"]);
        }

        $options = $params[1] ?? $data;

        if (is_object($options)) {
            /**
             * The options is a resultset
             */
            $using = $params["using"] ?? null;
            if (null === $using) {
                throw new Exception("The 'using' parameter is required");
            }

            if (!is_array($using) && !is_object($using)) {
                throw new Exception(
                    "The 'using' parameter should be an array"
                );
            }
        }

        unset($params["using"]);

        $code = BaseTag::renderAttributes("<select", $params) . ">" . PHP_EOL;

        if ($useEmpty) {
            /**
             * Create an empty value. Escape it the same way as the real
             * options so a crafted emptyValue/emptyText cannot inject markup.
             */
            $escaper = BaseTag::getEscaperService();
            $code .= self::echoOption($escaper->attributes(self::toStringValue($emptyValue)))
                . $escaper->html(self::toStringValue($emptyText))
                . self::OPTION_CLOSE . PHP_EOL;
        }

        if (is_object($options)) {
            /**
             * Create the SELECT's option from a resultset
             */
            $code .= self::optionsFromResultset(
                $options,
                $using,
                $value,
                self::OPTION_CLOSE . PHP_EOL
            );
        } else {
            if (is_array($options)) {
                /**
                 * Create the SELECT's option from an array
                 */
                $code .= self::optionsFromArray(
                    $options,
                    $value,
                    self::OPTION_CLOSE . PHP_EOL
                );
            }
        }

        $code .= self::SELECT_CLOSE;

        return $code;
    }

    protected static function echoOption(string $value, bool $selected = false): string
    {
        $extra = $selected ? 'selected="selected" ' : '';

        return "\t<option {$extra}value=\"" . $value . "\">";
    }

    /**
     * Reduces an arbitrary option value to the string the markup needs.
     * Option data is user supplied, so anything that cannot be expressed as
     * a string reads back as an empty string rather than aborting the tag.
     */
    protected static function toStringValue(mixed $value): string
    {
        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return "";
    }

    /**
     * Generate the OPTION tags based on an array
     *
     * @phpstan-param tag_select_data $data
     */
    private static function optionsFromArray(
        array $data,
        mixed $value,
        string $closeOption
    ): string {
        $code    = "";
        $escaper = BaseTag::getEscaperService();

        foreach ($data as $optionValue => $optionText) {
            $escaped = $escaper->attributes((string)$optionValue);

            if (is_array($optionText)) {
                $code .= "\t<optgroup label=\""
                    . $escaped . "\">" . PHP_EOL
                    . self::optionsFromArray($optionText, $value, $closeOption)
                    . "\t</optgroup>" . PHP_EOL;

                continue;
            }

            $escapedText = $escaper->html(self::toStringValue($optionText));

            if (is_array($value)) {
                if (true === in_array($optionValue, $value)) {
                    $code .= self::echoOption($escaped, true)
                        . $escapedText . $closeOption;
                } else {
                    $code .= self::echoOption($escaped)
                        . $escapedText . $closeOption;
                }
            } else {
                $strOptionValue = (string)$optionValue;
                $strValue       = self::toStringValue($value);

                if ($strOptionValue === $strValue) {
                    $code .= self::echoOption($escaped, true)
                        . $escapedText . $closeOption;
                } else {
                    $code .= self::echoOption($escaped)
                        . $escapedText . $closeOption;
                }
            }
        }

        return $code;
    }

    /**
     * Generate the OPTION tags based on a resultset
     *
     * ResultsetInterface does not extend Traversable, but every resultset
     * this is handed is iterable - the concrete Resultset implements
     * Iterator. The local annotation below states what the contract omits.
     */
    private static function optionsFromResultset(
        ResultsetInterface $resultset,
        mixed $using,
        mixed $value,
        string $closeOption
    ): string {
        $code      = "";
        $params    = null;
        $usingZero = '';
        $usingOne  = '';

        if (is_array($using)) {
            if (count($using) !== 2) {
                throw new Exception("Parameter 'using' requires two values");
            }

            $usingZero = self::toStringValue($using[0]);
            $usingOne  = self::toStringValue($using[1]);
        }

        $escaper = BaseTag::getEscaperService();

        /** @var iterable<array-key, mixed>&ResultsetInterface $rows */
        $rows = $resultset;

        foreach ($rows as $option) {
            if (is_array($using)) {
                if (is_object($option)) {
                    if (true === method_exists($option, "readAttribute")) {
                        $optionValue = $option->readAttribute($usingZero);
                        $optionText  = $option->readAttribute($usingOne);
                    } else {
                        $optionValue = $option->{$usingZero};
                        $optionText  = $option->{$usingOne};
                    }
                } else {
                    if (!is_array($option)) {
                        throw new Exception(
                            "Resultset returned an invalid value"
                        );
                    }

                    $optionValue = $option[$usingZero];
                    $optionText  = $option[$usingOne];
                }

                $optionValue = $escaper->attributes(self::toStringValue($optionValue));
                $optionText  = $escaper->html(self::toStringValue($optionText));

                /**
                 * If the value is equal to the option's value we mark it as
                 * selected
                 */
                if (is_array($value)) {
                    if (true === in_array($optionValue, $value)) {
                        $code .= self::echoOption($optionValue, true)
                            . $optionText
                            . $closeOption;
                    } else {
                        $code .= self::echoOption($optionValue)
                            . $optionText
                            . $closeOption;
                    }
                } else {
                    $strOptionValue = $optionValue;
                    $strValue       = self::toStringValue($value);

                    if ($strOptionValue === $strValue) {
                        $code .= self::echoOption($strOptionValue, true)
                            . $optionText
                            . $closeOption;
                    } else {
                        $code .= self::echoOption($strOptionValue)
                            . $optionText . $closeOption;
                    }
                }
            } else {
                /**
                 * Render through the developer-supplied closure. Restricting
                 * this to Closure (rather than any object) keeps the invoked
                 * callable out of reach of user-controlled data: a closure is
                 * defined in application code and can never be a name built
                 * from request input.
                 */
                if ($using instanceof Closure) {
                    if (null === $params) {
                        $params = [];
                    }

                    $params[0] = $option;
                    $executed  = call_user_func_array($using, $params);
                    $code      .= self::toStringValue($executed);
                }
            }
        }

        return $code;
    }
}
