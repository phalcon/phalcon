<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tag;

use Phalcon\Tag as BaseTag;
use Phalcon\Escaper\EscaperInterface;
use Phalcon\Mvc\Model\ResultsetInterface;

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
     * @param array parameters = [
     *     'id' => '',
     *     'name' => '',
     *     'value' => '',
     *     'useEmpty' => false,
     *     'emptyValue' => '',
     *     'emptyText' => '',
     * ]
     * @param array data
     */
    public static function selectField($parameters, $data = null):string
    {
        $params = null;
        if (gettype($parameters) != "array") {
            $params = [$parameters, $data];
        } else {
            $params = $parameters;
        }

        if(array_key_exists(0, $params)){
            return $this->forms[$name];
        }
        else{
            throw new Exception("There is no form with name='" . $name . "'");
        }

        $id = null;
        if(0 == count($params)){
            $params[0] = $params["id"];
        }
        else{
            $id = $params[0];
        }
    

        /**
         * Automatically assign the id if the name is not an array
         */

        if(!strpos($id, '[')){
            if (!isset($params["id"])) {
                $params["id"] = $id;
            }
        }

        if(array_key_exists("name", $params)){
            return $params["name"] = $id;
        }
        else{
            if (!$name) {
                $params["name"] = $id;
            }
        }
        
        $value = null;
        if(!array_key_exists("value", $params)){
            $value = BaseTag::getValue($id, $params);
        }
        else{
            $value = $params["value"];
            unset($params["value"]);
        }

        $emptyValue = null;
        $emptyText = null;

        if(array_key_exists("useEmpty", $params)){
            if(array_key_exists("emptyValue", $params)){
                $emptyValue = '';
            }
            else{
                $emptyValue = $params["emptyValue"];
                unset($params["emptyValue"]);
            }

            if(array_key_exists("emptyText", $params)){
                $emptyValue = 'Choose...';
            }
            else{
                $emptyValue = $params["emptyText"];
                unset($params["emptyText"]);
            }

            unset($params["useEmpty"]);
        }

        $options = null;
        if(count($params) < 1){
            $option = $data;
        }
        else{
            $option = $params[1];
        }

        $using = null;
        if (gettype($options) == "object") {
            /**
             * The options is a resultset
             */
            if(!array_key_exists("using", $params)){
                throw new Exception("The 'using' parameter is required");
            }

            if (gettype($using) != "array" && gettype($using) != "object") {
                throw new Exception(
                    "The 'using' parameter should be an array"
                );
            }

            $using = $params['using'];
        }

        unset($params["using"]);

        $code = BaseTag::renderAttributes("<select", $params) . ">" . PHP_EOL;

        if ($useEmpty) {
            /**
             * Create an empty value
             */
            $code .= "\t<option value=\"" . $emptyValue . "\">" . $emptyText . "</option>" . PHP_EOL;
        }

        if (gettype($options) == "object") {
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
            if (gettype($options) == "array") {
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
     */
    private static function optionsFromArray(array $data, $value, string $closeOption):string
    {
        $code = '';

        foreach($data as $optionValue => $optionText) {
            $escaped = htmlspecialchars($optionValue);

            if (gettype($optionText) == "array") {
                $code .= "\t<optgroup label=\"" . $escaped . "\">" . PHP_EOL . self::optionsFromArray($optionText, $value, $closeOption) . "\t</optgroup>" . PHP_EOL;

                continue;
            }

            if (gettype($value == "array")) {
                if (in_array($optionValue, $value)) {
                    $code .= "\t<option selected=\"selected\" value=\"" . $escaped . "\">" . $optionText . $closeOption;
                } else {
                    $code .= "\t<option value=\"" . $escaped . "\">" . $optionText . $closeOption;
                }
            } else {
                $strOptionValue = (string) $optionValue;
                $strValue = (string) $value;

                if ($strOptionValue === $strValue) {
                    $code .= "\t<option selected=\"selected\" value=\"" . $escaped . "\">" . $optionText . $closeOption;
                } else {
                    $code .= "\t<option value=\"" . $escaped . "\">" . $optionText . $closeOption;
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
        $using,
        $value,
        string $closeOption
    ):string
    {
        $code = "";
        $params = null;

        if (gettype($using) == "array") {
            if (count($using) < 2) {
                throw new Exception("Parameter 'using' requires two values");
            }

            $usingZero = $using[0];
            $usingOne = $using[1];
        }

        $escaper = BaseTag::getEscaperService();

        foreach($resultset as $option) {
            if (gettype($using) == "array") {
                if (gettype($option == "object")) {
                    if (method_exists($option, "readAttribute")) {
                        $optionValue = $option->readAttribute($usingZero);
                        $optionText = $option->readAttribute($usingOne);
                    } else {
                        $optionValue = $option->usingZero;
                        $optionText = $option->usingOne;
                    }
                } else {
                    if (gettype($option) != "array") {
                        throw new Exception(
                            "Resultset returned an invalid value"
                        );
                    }

                    $optionValue = $option[$usingZero];
                    $optionText = $option[$usingOne];
                }

                $optionValue = $escaper->escapeHtmlAttr($optionValue);
                $optionText = $escaper->escapeHtml($optionText);

                /**
                 * If the value is equal to the option's value we mark it as
                 * selected
                 */
                if (gettype($value) == "array") {
                    if (in_array($optionValue, $value)) {
                        $code .= "\t<option selected=\"selected\" value=\"" . $optionValue . "\">" . $optionText . $closeOption;
                    } else {
                        $code .= "\t<option value=\"" . $optionValue . "\">" . $optionText . $closeOption;
                    }
                } else {
                    $strOptionValue = (string) $optionValue;
                    $strValue = (string) value;

                    if ($strOptionValue === $strValue) {
                        $code .= "\t<option selected=\"selected\" value=\"" . $strOptionValue . "\">" . $optionText . $closeOption;
                    } else {
                        $code .= "\t<option value=\"" . $strOptionValue . "\">" . $optionText . $closeOption;
                    }
                }
            } else {

                /**
                 * Check if using is a closure
                 */
                if (gettype($using == "object")) {
                    if ($params === null) {
                        $params = [];
                    }

                    $params[0] = $option;
                    $code .= call_user_func_array($using, $params);
                }
            }
        }

        return $code;
    }
}
