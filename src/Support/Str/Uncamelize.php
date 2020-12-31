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
 *  Called from Route, Model, Manager, Router/Annotations.
 *  This is a stop - gap filler for Phalcon 5.0.
 * No second argument for spacer character.
 * Phalcon\Text::uncamelize("CocoBongo"); // coco_bongo
 * Phalcon\Text::uncamelize("CocoBongo", "-"); // coco-bongo
*/



class Uncamelize {
    
    static function fn(string $str) : string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', 
                                    '/([^_])([A-Z][a-z])/'], 
                                    '$1_$2', 
                                    $str));
    }
	public function __invoke(
        string $text,
    ): string {
        return self::fn($text);
    }
}

