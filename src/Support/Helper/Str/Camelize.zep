
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Support\Helper\Str;

use Phalcon\Support\Exception;

/**
 * Camelize a string
 */
class Camelize
{
    /**
     * Converts strings to camelize style
     *
     * ```php
     * use Phalcon\Support\Helper\Str\Camelize;
     *
     * $object = new Camelize();
     * echo $object("coco_bongo");                   // CocoBongo
     * echo $object("co_co-bon_go", "-");            // Co_coBon_go
     * echo $object->__invoke("co_co-bon_go", "_-"); // CoCoBonGo
     * ```
     *
     * @param string $message
     * @param mixed  $delimiter
     *
     * @return string
     */
    public function __invoke(
        string message,
        string delimiter = "_-"
    ) -> string {
        var character, delimiterArray, found = false, messageArray;
        array resultArray = [];

        if empty delimiter {
            throw new Exception("The delimiter cannot be an empty value");
        }

        let delimiterArray = array_flip(str_split(delimiter)),
            messageArray   = str_split(message);

        for character in messageArray {
            if array_key_exists(character, delimiter) {
                let found = true;
                continue;
            }

            if found {
                let resultArray[] = mb_strtoupper(character),
                    found         = false;
            } else {
                let resultArray[] = mb_strtolower(character);
            }
        }

        return implode("", resultArray);
    }
}
