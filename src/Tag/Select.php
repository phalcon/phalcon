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
        var params, name, id, value, useEmpty, code, emptyValue, emptyText,
            options, using;

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
            throw new Exception("There is no form with name='" . name . "'");
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

            unset $params["useEmpty"];
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
            if typeof options == "array" {
                /**
                 * Create the SELECT's option from an array
                 */
                let code .= self::optionsFromArray(
                    options,
                    value,
                    "</option>" . PHP_EOL
                );
            }
        }

        let code .= "</select>";

        return code;
    }

    /**
     * Generate the OPTION tags based on an array
     */
    private static function optionsFromArray(array data, var value, string closeOption) -> string
    {
        var strValue, strOptionValue, code, optionValue, optionText, escaped;

        let code = "";

        for optionValue, optionText in data {
            let escaped = htmlspecialchars(optionValue);

            if typeof optionText == "array" {
                let code .= "\t<optgroup label=\"" . escaped . "\">" . PHP_EOL . self::optionsFromArray(optionText, value, closeOption) . "\t</optgroup>" . PHP_EOL;

                continue;
            }

            if typeof value == "array" {
                if in_array(optionValue, value) {
                    let code .= "\t<option selected=\"selected\" value=\"" . escaped . "\">" . optionText . closeOption;
                } else {
                    let code .= "\t<option value=\"" . escaped . "\">" . optionText . closeOption;
                }
            } else {
                let strOptionValue = (string) optionValue,
                    strValue = (string) value;

                if strOptionValue === strValue {
                    let code .= "\t<option selected=\"selected\" value=\"" . escaped . "\">" . optionText . closeOption;
                } else {
                    let code .= "\t<option value=\"" . escaped . "\">" . optionText . closeOption;
                }
            }
        }

        return code;
    }

    /**
     * Generate the OPTION tags based on a resultset
     *
     * @param array using
     */
    private static function optionsFromResultset(
        <ResultsetInterface> resultset,
        var using,
        var value,
        string closeOption
    ) -> string
    {
        var code, params, option, usingZero, usingOne, escaper, optionValue,
            optionText, strValue, strOptionValue;

        let code = "";
        let params = null;

        if typeof using == "array" {
            if unlikely count(using) != 2 {
                throw new Exception("Parameter 'using' requires two values");
            }

            let usingZero = using[0],
                usingOne = using[1];
        }

        let escaper = <EscaperInterface> BaseTag::getEscaperService();

        for option in iterator(resultset) {
            if typeof using == "array" {
                if typeof option == "object" {
                    if method_exists(option, "readAttribute") {
                        let optionValue = option->readAttribute(usingZero);
                        let optionText = option->readAttribute(usingOne);
                    } else {
                        let optionValue = option->usingZero;
                        let optionText = option->usingOne;
                    }
                } else {
                    if unlikely typeof option != "array" {
                        throw new Exception(
                            "Resultset returned an invalid value"
                        );
                    }

                    let optionValue = option[usingZero];
                    let optionText = option[usingOne];
                }

                let optionValue = escaper->escapeHtmlAttr(optionValue);
                let optionText = escaper->escapeHtml(optionText);

                /**
                 * If the value is equal to the option's value we mark it as
                 * selected
                 */
                if typeof value == "array" {
                    if in_array(optionValue, value) {
                        let code .= "\t<option selected=\"selected\" value=\"" . optionValue . "\">" . optionText . closeOption;
                    } else {
                        let code .= "\t<option value=\"" . optionValue . "\">" . optionText . closeOption;
                    }
                } else {
                    let strOptionValue = (string) optionValue,
                        strValue = (string) value;

                    if strOptionValue === strValue {
                        let code .= "\t<option selected=\"selected\" value=\"" . strOptionValue . "\">" . optionText . closeOption;
                    } else {
                        let code .= "\t<option value=\"" . strOptionValue . "\">" . optionText . closeOption;
                    }
                }
            } else {

                /**
                 * Check if using is a closure
                 */
                if typeof using == "object" {
                    if params === null {
                        let params = [];
                    }

                    let params[0] = option;
                    let code .= call_user_func_array(using, params);
                }
            }
        }

        return code;
    }
}
