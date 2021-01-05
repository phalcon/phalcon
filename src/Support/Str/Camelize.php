<?php

namespace Phalcon\Support\Str;

use function preg_replace;
use function strtolower;
/** 
 * 
 * Class Uncamelize
 *
 * @package Phalcon\Support\Str
 *
 * Previously done by builtin C encoded function with zephir.
 * Code from first return googled on stackoverflow.
 * echo Str::camelize("coco_bongo");            // CocoBongo
 * echo Str::camelize("co_co-bon_go", "-");     // Co_coBon_go
 * echo Str::camelize("co_co-bon_go", "_");    // CoCo-BonGo
 * This version only does a single string sequence delimiter,
 * due to very same limit of PHP explode.
*/



class Camelize {

    static function fn(string $str, string $delimiter = '_') : string
    {
        $words = explode($delimiter, $str);
        $words = array_map('ucfirst', $words);
        return implode('', $words);
    }
	public function __invoke(
        string $text, string $delimiter = '_'
    ): string {
        return self::fn($text, $delimiter);
    }
}