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

namespace Phalcon\Parsers\Volt;

/**
 * Constants
 */
class Enum
{
    public const PHVOLT_MODE_CODE    = 1;
    public const PHVOLT_MODE_COMMENT = 2;
    /** Modes */
    public const PHVOLT_MODE_RAW = 0;
    /**
     * VOLT
     */
    public const PHVOLT_RAW_BUFFER_SIZE            = 256;
    public const PHVOLT_SCANNER_RETCODE_EOF        = -1;
    public const PHVOLT_SCANNER_RETCODE_ERR        = -2;
    public const PHVOLT_SCANNER_RETCODE_IMPOSSIBLE = -3;
    public const PHVOLT_T_ADD                      = 43; //'+';

    /* Literals & Identifiers */
    public const PHVOLT_T_ADD_ASSIGN  = 281;
    public const PHVOLT_T_AND         = 266;
    public const PHVOLT_T_ARRAY       = 360;
    public const PHVOLT_T_ARRAYACCESS = 361;
    public const PHVOLT_T_ASSIGN      = 61; //'=';
    public const PHVOLT_T_AUTOESCAPE  = 317;
    public const PHVOLT_T_BLOCK       = 307;

    /* Operators */
    public const PHVOLT_T_BREAK            = 320;
    public const PHVOLT_T_CACHE            = 314;
    public const PHVOLT_T_CALL             = 325;
    public const PHVOLT_T_CASE             = 412;
    public const PHVOLT_T_CBRACKET_CLOSE   = 125; //'}';
    public const PHVOLT_T_CBRACKET_OPEN    = 123; //'{';
    public const PHVOLT_T_CLOSE_DELIMITER  = 331;
    public const PHVOLT_T_CLOSE_EDELIMITER = 333;
    public const PHVOLT_T_COLON            = 277;
    public const PHVOLT_T_COMMA            = 269;
    public const PHVOLT_T_CONCAT           = 126; //'~';
    public const PHVOLT_T_CONTINUE         = 319;
    public const PHVOLT_T_DECR             = 280;
    public const PHVOLT_T_DEFAULT          = 413;
    public const PHVOLT_T_DEFINED          = 312;
    public const PHVOLT_T_DIV              = 47; //'/';
    public const PHVOLT_T_DIV_ASSIGN       = 284;
    public const PHVOLT_T_DO               = 316;
    public const PHVOLT_T_DOT              = 46; //'.';
    public const PHVOLT_T_DOUBLE           = 259;
    public const PHVOLT_T_ECHO             = 359;
    public const PHVOLT_T_ELSE             = 301;
    public const PHVOLT_T_ELSEFOR          = 321;
    public const PHVOLT_T_ELSEIF           = 302;
    public const PHVOLT_T_EMPTY            = 380;
    public const PHVOLT_T_EMPTY_STATEMENT  = 358;
    public const PHVOLT_T_ENCLOSED         = 356;
    public const PHVOLT_T_ENDAUTOESCAPE    = 318;
    public const PHVOLT_T_ENDBLOCK         = 308;
    public const PHVOLT_T_ENDCACHE         = 315;
    public const PHVOLT_T_ENDCALL          = 326;
    public const PHVOLT_T_ENDFOR           = 305;
    public const PHVOLT_T_ENDIF            = 303;
    public const PHVOLT_T_ENDMACRO         = 323;
    public const PHVOLT_T_ENDRAW           = 401;
    public const PHVOLT_T_ENDSWITCH        = 414;
    public const PHVOLT_T_EQUALS           = 272;
    public const PHVOLT_T_EVEN             = 381;
    public const PHVOLT_T_EXPR             = 354;
    public const PHVOLT_T_EXTENDS          = 310;
    public const PHVOLT_T_FALSE            = 262;
    /** Special Tokens */
    public const PHVOLT_T_FCALL        = 350;
    public const PHVOLT_T_FOR          = 304;
    public const PHVOLT_T_GREATER      = 62; //'>';
    public const PHVOLT_T_GREATEREQUAL = 271;
    public const PHVOLT_T_IDENTICAL    = 274;
    public const PHVOLT_T_IDENTIFIER   = 265;
    /** Reserved words */
    public const PHVOLT_T_IF             = 300;
    public const PHVOLT_T_IGNORE         = 257;
    public const PHVOLT_T_IN             = 309;
    public const PHVOLT_T_INCLUDE        = 313;
    public const PHVOLT_T_INCR           = 279;
    public const PHVOLT_T_INTEGER        = 258;
    public const PHVOLT_T_IS             = 311;
    public const PHVOLT_T_ISEMPTY        = 386;
    public const PHVOLT_T_ISEVEN         = 387;
    public const PHVOLT_T_ISITERABLE     = 391;
    public const PHVOLT_T_ISNUMERIC      = 389;
    public const PHVOLT_T_ISODD          = 388;
    public const PHVOLT_T_ISSCALAR       = 390;
    public const PHVOLT_T_ISSET          = 363;
    public const PHVOLT_T_ITERABLE       = 385;
    public const PHVOLT_T_LESS           = 60; //'<';
    public const PHVOLT_T_LESSEQUAL      = 270;
    public const PHVOLT_T_MACRO          = 322;
    public const PHVOLT_T_MINUS          = 368;
    public const PHVOLT_T_MOD            = 37; //'%';
    public const PHVOLT_T_MUL            = 42; //'*';
    public const PHVOLT_T_MUL_ASSIGN     = 283;
    public const PHVOLT_T_NOT            = 33; //'!';
    public const PHVOLT_T_NOTEQUALS      = 273;
    public const PHVOLT_T_NOTIDENTICAL   = 275;
    public const PHVOLT_T_NOT_IN         = 367;
    public const PHVOLT_T_NOT_ISEMPTY    = 392;
    public const PHVOLT_T_NOT_ISEVEN     = 393;
    public const PHVOLT_T_NOT_ISITERABLE = 397;
    public const PHVOLT_T_NOT_ISNUMERIC  = 395;
    public const PHVOLT_T_NOT_ISODD      = 394;
    public const PHVOLT_T_NOT_ISSCALAR   = 396;
    public const PHVOLT_T_NOT_ISSET      = 362;
    public const PHVOLT_T_NULL           = 261;
    public const PHVOLT_T_NUMERIC        = 383;
    public const PHVOLT_T_ODD            = 382;
    /** Delimiters */
    public const PHVOLT_T_OPEN_DELIMITER    = 330;
    public const PHVOLT_T_OPEN_EDELIMITER   = 332;
    public const PHVOLT_T_OR                = 267;
    public const PHVOLT_T_PARENTHESES_CLOSE = 41;  //')';
    public const PHVOLT_T_PARENTHESES_OPEN  = 40;  //'(';
    public const PHVOLT_T_PIPE              = 124; //'|';
    public const PHVOLT_T_PLUS              = 369;
    public const PHVOLT_T_POW               = 278;
    public const PHVOLT_T_QUALIFIED         = 355;
    public const PHVOLT_T_QUESTION          = 63; //'?';
    public const PHVOLT_T_RANGE             = 276;
    public const PHVOLT_T_RAW               = 400;
    public const PHVOLT_T_RAW_FRAGMENT      = 357;
    public const PHVOLT_T_RESOLVED_EXPR     = 364;
    public const PHVOLT_T_RETURN            = 327;
    public const PHVOLT_T_SBRACKET_CLOSE    = 91; //']';
    public const PHVOLT_T_SBRACKET_OPEN     = 93; //'[';
    public const PHVOLT_T_SCALAR            = 384;
    public const PHVOLT_T_SET               = 306;
    public const PHVOLT_T_SLICE             = 365;
    public const PHVOLT_T_STRING            = 260;
    public const PHVOLT_T_SUB               = 45; //'-';
    public const PHVOLT_T_SUB_ASSIGN        = 282;

    /* switch/ -case statement */
    public const PHVOLT_T_SWITCH  = 411;
    public const PHVOLT_T_TERNARY = 366;
    public const PHVOLT_T_TRUE    = 263;
    public const PHVOLT_T_WITH    = 324;
}
