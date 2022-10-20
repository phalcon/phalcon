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

use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Tag as BaseTag;

use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function str_contains;

/**
 * Phalcon\Tag\Select
 *
 * Generates a SELECT HTML tag using a static array of values or a
 * Phalcon\Mvc\Model resultset
 */
abstract class Select
{
    /**
     * Generates a SELECT tag
     *
     * @param array|string $parameters = [
     *                                 'id'         => '',
     *                                 'name'       => '',
     *                                 'value'      => '',
     *                                 'useEmpty'   => false,
     *                                 'emptyValue' => '',
     *                                 'emptyText'  => '',
     *                                 ]
     * @param mixed|null   $data
     *
     * @return string
     * @throws Exception
     */
    public static function selectField(
        array|string $parameters,
        mixed $data = null
    ): string {
        $params = $parameters;
        if (true === is_string($params)) {
            $params = [$parameters, $data];
        }

        $id = null;
        if (true === isset($params[0])) {
            $id = $params[0];
        } else {
            $params[0] = $params["id"];
        }

        /**
         * Automatically assign the id if the name is not an array
         */
        if (!str_contains($id, "[")) {
            if (true !== isset($params["id"])) {
                $params["id"] = $id;
            }
        }

        if (true !== isset($params["name"])) {
            $params["name"] = $id;
        } else {
            $name = $params["name"];
            if (!$name) {
                $params["name"] = $id;
            }
        }

        $value = $params["value"] ?? BaseTag::getValue($id, $params);
        if (true === isset($params["value"])) {
            unset($params["value"]);
        }

        $useEmpty = $params["useEmpty"] ?? false;
        if (true === isset($params["useEmpty"])) {
            $emptyValue = $params["emptyValue"] ?? "";
            if (true === isset($params["emptyValue"])) {
                unset($params["emptyValue"]);
            }

            $emptyText = $params["emptyText"] ?? "Choose...";
            if (true === $params["emptyText"]) {
                unset($params["emptyText"]);
            }

            unset($params["useEmpty"]);
        }

        $options = $params[1] ?? $data;

        if (true === is_object($options)) {
            /**
             * The options parameter is a resultset
             */
            $using = $params["using"] ?? null;
            if (null === $using) {
                throw new Exception("The 'using' parameter is required");
            }

            if (true !== is_array($using) && true === is_string($using)) {
                throw new Exception(
                    "The 'using' parameter should be an array"
                );
            }
        }

        unset($params["using"]);

        $code = BaseTag::renderAttributes("<select", $params) . ">" . PHP_EOL;

        if ($useEmpty) {
            /**
             * Create an empty value
             */
            $code .= "\t<option value=\"" . $emptyValue . "\">" . $emptyText . "</option>" . PHP_EOL;
        }

        if (true === is_object($options)) {
            /**
             * Create the SELECT's option from a resultset
             */
            $code .= self::optionsFromResultset(
                $options,
                $using,
                $value,
                "</option>" . PHP_EOL
            );
        } else {
            if (true === is_array($options)) {
                /**
                 * Create the SELECT's option from an array
                 */
                $code .= self::optionsFromArray(
                    $options,
                    $value,
                    "</option>" . PHP_EOL
                );
            }
        }

        $code .= "</select>";

        return $code;
    }

    /**
     * Generate the OPTION tags based on an array
     *
     * @param array  $data
     * @param mixed  $value
     * @param string $closeOption
     *
     * @return string
     */
    private static function optionsFromArray(
        array $data,
        mixed $value,
        string $closeOption
    ): string {
        $code    = "";
        $escaper = BaseTag::getEscaperService();

        foreach ($data as $optionValue => $optionText) {
            $escaped = $escaper->html((string) $optionValue);
//            $escaped = htmlspecialchars($optionValue);

            if (true === is_array($optionText)) {
                $code .= "\t<optgroup label=\""
                    . $escaped . "\">" . PHP_EOL
                    . self::optionsFromArray($optionText, $value, $closeOption)
                    . "\t</optgroup>" . PHP_EOL;

                continue;
            }

            if (true === is_array($value)) {
                if (true === in_array($optionValue, $value)) {
                    $code .= "\t<option selected=\"selected\" value=\""
                        . $escaped . "\">" . $optionText . $closeOption;
                } else {
                    $code .= "\t<option value=\"" . $escaped . "\">"
                        . $optionText . $closeOption;
                }
            } else {
                $strOptionValue = (string) $optionValue;
                $strValue       = (string) $value;

                if ($strOptionValue === $strValue) {
                    $code .= "\t<option selected=\"selected\" value=\""
                        . $escaped . "\">" . $optionText . $closeOption;
                } else {
                    $code .= "\t<option value=\"" . $escaped . "\">"
                        . $optionText . $closeOption;
                }
            }
        }

        return $code;
    }

    /**
     * Generate the OPTION tags based on a resultset
     *
     * @param array using
     */
    private static function optionsFromResultset(
        ResultsetInterface $resultset,
        mixed $using,
        mixed $value,
        string $closeOption
    ): string {
        $code   = "";
        $params = null;

        if (true === is_array($using)) {
            if (count($using) !== 2) {
                throw new Exception("Parameter 'using' requires two values");
            }

            $usingZero = $using[0];
            $usingOne  = $using[1];
        }

        $escaper = BaseTag::getEscaperService();
        foreach ($resultset as $option) {
            if (true === is_array($using)) {
                if (true === is_object($option)) {
                    if (true === method_exists($option, "readAttribute")) {
                        $optionValue = $option->readAttribute($usingZero);
                        $optionText  = $option->readAttribute($usingOne);
                    } else {
                        $optionValue = $option->usingZero;
                        $optionText  = $option->usingOne;
                    }
                } else {
                    if (true !== is_array($option)) {
                        throw new Exception(
                            "Resultset returned an invalid value"
                        );
                    }

                    $optionValue = $option[$usingZero];
                    $optionText  = $option[$usingOne];
                }

                $optionValue = $escaper->attributes($optionValue);
                $optionText  = $escaper->html($optionText);

                /**
                 * If the value is equal to the option's value we mark it as
                 * selected
                 */
                if (true === is_array($value)) {
                    if (true === in_array($optionValue, $value)) {
                        $code .= "\t<option selected=\"selected\" value=\""
                            . $optionValue . "\">"
                            . $optionText
                            . $closeOption;
                    } else {
                        $code .= "\t<option value=\""
                            . $optionValue . "\">"
                            . $optionText
                            . $closeOption;
                    }
                } else {
                    $strOptionValue = (string) $optionValue;
                    $strValue       = (string) $value;

                    if ($strOptionValue === $strValue) {
                        $code .= "\t<option selected=\"selected\" value=\""
                            . $strOptionValue . "\">"
                            . $optionText
                            . $closeOption;
                    } else {
                        $code .= "\t<option value=\"" . $strOptionValue
                            . "\">" . $optionText . $closeOption;
                    }
                }
            } else {

                /**
                 * Check if using is a closure
                 */
                if (true === is_object($using)) {
                    if (null === $params) {
                        $params = [];
                    }

                    $params[0] = $option;
                    $code      .= call_user_func_array($using, $params);
                }
            }
        }

        return $code;
    }
}
