<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Str;

use Phalcon\Traits\Helper\Str\LowerTrait;

use function array_keys;
use function array_values;
use function is_string;
use function preg_replace;
use function str_replace;
use function trim;

/**
 * Changes a text to a URL friendly one. Replaces commonly known accented
 * characters with their Latin equivalents. If a `replace` string or array
 * is passed, it will also be used to replace those characters with a space.
 */
class Friendly
{
    use LowerTrait;

    /**
     * @param string       $text
     * @param string       $separator
     * @param bool         $lowercase
     * @param array|string $replace
     *
     * @return string
     */
    public function __invoke(
        string $text,
        string $separator = '-',
        bool $lowercase = true,
        array | string $replace = []
    ): string {
        if (true === is_string($replace)) {
            $replace = [$replace];
        }

        $matrix = $this->getMatrix($replace);

        $text     = str_replace(array_keys($matrix), array_values($matrix), $text);
        $friendly = (string)preg_replace(
            "/[^a-zA-Z0-9\\/_|+ -]/",
            "",
            $text
        );

        if ($lowercase) {
            $friendly = $this->toLower($friendly);
        }

        $friendly = preg_replace("/[\\/_|+ -]+/", $separator, $friendly);

        return trim($friendly, $separator);
    }

    /**
     * @param array<string, string> $replace
     *
     * @return array
     */
    private function getMatrix(array $replace): array
    {
        $matrix = [
            "Š"    => "S",
            "š"    => "s",
            "Đ"    => "Dj",
            "Ð"    => "Dj",
            "đ"    => "dj",
            "Ž"    => "Z",
            "ž"    => "z",
            "Č"    => "C",
            "č"    => "c",
            "Ć"    => "C",
            "ć"    => "c",
            "À"    => "A",
            "Á"    => "A",
            "Â"    => "A",
            "Ã"    => "A",
            "Ä"    => "A",
            "Å"    => "A",
            "Æ"    => "A",
            "Ç"    => "C",
            "È"    => "E",
            "É"    => "E",
            "Ê"    => "E",
            "Ë"    => "E",
            "Ì"    => "I",
            "Í"    => "I",
            "Î"    => "I",
            "Ï"    => "I",
            "Ñ"    => "N",
            "Ò"    => "O",
            "Ó"    => "O",
            "Ô"    => "O",
            "Õ"    => "O",
            "Ö"    => "O",
            "Ø"    => "O",
            "Ù"    => "U",
            "Ú"    => "U",
            "Û"    => "U",
            "Ü"    => "U",
            "Ý"    => "Y",
            "Þ"    => "B",
            "ß"    => "Ss",
            "à"    => "a",
            "á"    => "a",
            "â"    => "a",
            "ã"    => "a",
            "ä"    => "a",
            "å"    => "a",
            "æ"    => "a",
            "ç"    => "c",
            "è"    => "e",
            "é"    => "e",
            "ê"    => "e",
            "ë"    => "e",
            "ì"    => "i",
            "í"    => "i",
            "î"    => "i",
            "ï"    => "i",
            "ð"    => "o",
            "ñ"    => "n",
            "ò"    => "o",
            "ó"    => "o",
            "ô"    => "o",
            "õ"    => "o",
            "ö"    => "o",
            "ø"    => "o",
            "ù"    => "u",
            "ú"    => "u",
            "û"    => "u",
            "ý"    => "y",
            "þ"    => "b",
            "ÿ"    => "y",
            "Ŕ"    => "R",
            "ŕ"    => "r",
            "ē"    => "e",
            "'"    => "",
            "&"    => " and ",
            "\r\n" => " ",
            "\n"   => " ",
        ];

        foreach ($replace as $item) {
            $matrix[$item] = " ";
        }

        return $matrix;
    }
}
