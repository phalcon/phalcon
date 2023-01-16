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

namespace Phalcon\Parsers;

/**
 * Constants
 */
class Enum
{
    /**
     * ANNOTATIONS
     */
    public const PHANNOT_SCANNER_RETCODE_EOF        = -1;
    public const PHANNOT_SCANNER_RETCODE_ERR        = -2;
    public const PHANNOT_SCANNER_RETCODE_IMPOSSIBLE = -3;

    /** Modes */
    public const PHANNOT_MODE_RAW        = 0;
    public const PHANNOT_MODE_ANNOTATION = 1;

    public const PHANNOT_T_IGNORE = 297;

    public const PHANNOT_T_DOCBLOCK_ANNOTATION = 299;
    public const PHANNOT_T_ANNOTATION          = 300;

    /* Literals & Identifiers */
    public const PHANNOT_T_INTEGER        = 301;
    public const PHANNOT_T_DOUBLE         = 302;
    public const PHANNOT_T_STRING         = 303;
    public const PHANNOT_T_NULL           = 304;
    public const PHANNOT_T_FALSE          = 305;
    public const PHANNOT_T_TRUE           = 306;
    public const PHANNOT_T_IDENTIFIER     = 307;
    public const PHANNOT_T_ARRAY          = 308;
    public const PHANNOT_T_ARBITRARY_TEXT = 309;

    /* Operators */
    public const PHANNOT_T_AT                = "@";
    public const PHANNOT_T_DOT               = ".";
    public const PHANNOT_T_COMMA             = ",";
    public const PHANNOT_T_EQUALS            = "=";
    public const PHANNOT_T_COLON             = ":";
    public const PHANNOT_T_BRACKET_OPEN      = "{";
    public const PHANNOT_T_BRACKET_CLOSE     = "}";
    public const PHANNOT_T_SBRACKET_OPEN     = "[";
    public const PHANNOT_T_SBRACKET_CLOSE    = "]";
    public const PHANNOT_T_PARENTHESES_OPEN  = "(";
    public const PHANNOT_T_PARENTHESES_CLOSE = ")";

    /**
     * PHQL
     */
    public const PHQL_SCANNER_RETCODE_EOF        = -1;
    public const PHQL_SCANNER_RETCODE_ERR        = -2;
    public const PHQL_SCANNER_RETCODE_IMPOSSIBLE = -3;

    public const PHQL_T_IGNORE = 257;

    /* Literals & Identifiers */
    public const PHQL_T_INTEGER    = 258;
    public const PHQL_T_DOUBLE     = 259;
    public const PHQL_T_STRING     = 260;
    public const PHQL_T_IDENTIFIER = 265;
    public const PHQL_T_HINTEGER   = 414;

    /* Operators */
    public const PHQL_T_ADD         = '+';
    public const PHQL_T_SUB         = '-';
    public const PHQL_T_MUL         = '*';
    public const PHQL_T_DIV         = '/';
    public const PHQL_T_MOD         = '%';
    public const PHQL_T_BITWISE_AND = '&';
    public const PHQL_T_BITWISE_OR  = '|';
    public const PHQL_T_BITWISE_XOR = '^';
    public const PHQL_T_BITWISE_NOT = '~';
    public const PHQL_T_AND         = 266;
    public const PHQL_T_OR          = 267;
    public const PHQL_T_LIKE        = 268;
    public const PHQL_T_ILIKE       = 275;
    public const PHQL_T_AGAINST     = 276;

    public const PHQL_T_DOT   = '.';
    public const PHQL_T_COMMA = 269;
    public const PHQL_T_COLON = ':';

    public const PHQL_T_EQUALS       = '=';
    public const PHQL_T_NOTEQUALS    = 270;
    public const PHQL_T_NOT          = '!';
    public const PHQL_T_LESS         = '<';
    public const PHQL_T_LESSEQUAL    = 271;
    public const PHQL_T_GREATER      = '>';
    public const PHQL_T_GREATEREQUAL = 272;

    public const PHQL_T_PARENTHESES_OPEN  = '(';
    public const PHQL_T_PARENTHESES_CLOSE = ')';

    /** Placeholders */
    public const PHQL_T_NPLACEHOLDER = 273;
    public const PHQL_T_SPLACEHOLDER = 274;
    public const PHQL_T_BPLACEHOLDER = 277;

    /** Reserved words */
    public const PHQL_T_UPDATE      = 300;
    public const PHQL_T_SET         = 301;
    public const PHQL_T_WHERE       = 302;
    public const PHQL_T_DELETE      = 303;
    public const PHQL_T_FROM        = 304;
    public const PHQL_T_AS          = 305;
    public const PHQL_T_INSERT      = 306;
    public const PHQL_T_INTO        = 307;
    public const PHQL_T_VALUES      = 308;
    public const PHQL_T_SELECT      = 309;
    public const PHQL_T_ORDER       = 310;
    public const PHQL_T_BY          = 311;
    public const PHQL_T_LIMIT       = 312;
    public const PHQL_T_GROUP       = 313;
    public const PHQL_T_HAVING      = 314;
    public const PHQL_T_IN          = 315;
    public const PHQL_T_ON          = 316;
    public const PHQL_T_INNER       = 317;
    public const PHQL_T_JOIN        = 318;
    public const PHQL_T_LEFT        = 319;
    public const PHQL_T_RIGHT       = 320;
    public const PHQL_T_IS          = 321;
    public const PHQL_T_NULL        = 322;
    public const PHQL_T_NOTIN       = 323;
    public const PHQL_T_CROSS       = 324;
    public const PHQL_T_FULL        = 325;
    public const PHQL_T_OUTER       = 326;
    public const PHQL_T_ASC         = 327;
    public const PHQL_T_DESC        = 328;
    public const PHQL_T_OFFSET      = 329;
    public const PHQL_T_DISTINCT    = 330;
    public const PHQL_T_BETWEEN     = 331;
    public const PHQL_T_BETWEEN_NOT = 332;
    public const PHQL_T_CAST        = 333;
    public const PHQL_T_TRUE        = 334;
    public const PHQL_T_FALSE       = 335;
    public const PHQL_T_CONVERT     = 336;
    public const PHQL_T_USING       = 337;
    public const PHQL_T_ALL         = 338;
    public const PHQL_T_FOR         = 339;

    /** Special Tokens */
    public const PHQL_T_FCALL         = 350;
    public const PHQL_T_NLIKE         = 351;
    public const PHQL_T_STARALL       = 352;
    public const PHQL_T_DOMAINALL     = 353;
    public const PHQL_T_EXPR          = 354;
    public const PHQL_T_QUALIFIED     = 355;
    public const PHQL_T_ENCLOSED      = 356;
    public const PHQL_T_NILIKE        = 357;
    public const PHQL_T_RAW_QUALIFIED = 358;

    public const PHQL_T_INNERJOIN = 360;
    public const PHQL_T_LEFTJOIN  = 361;
    public const PHQL_T_RIGHTJOIN = 362;
    public const PHQL_T_CROSSJOIN = 363;
    public const PHQL_T_FULLJOIN  = 364;
    public const PHQL_T_ISNULL    = 365;
    public const PHQL_T_ISNOTNULL = 366;
    public const PHQL_T_MINUS     = 367;

    /** Postgresql Text Search Operators */
    public const PHQL_T_TS_MATCHES          = 401;
    public const PHQL_T_TS_OR               = 402;
    public const PHQL_T_TS_AND              = 403;
    public const PHQL_T_TS_NEGATE           = 404;
    public const PHQL_T_TS_CONTAINS_ANOTHER = 405;
    public const PHQL_T_TS_CONTAINS_IN      = 406;

    public const PHQL_T_SUBQUERY = 407;
    public const PHQL_T_EXISTS   = 408;

    public const PHQL_T_CASE = 409;
    public const PHQL_T_WHEN = 410;
    public const PHQL_T_ELSE = 411;
    public const PHQL_T_END  = 412;
    public const PHQL_T_THEN = 413;
    public const PHQL_T_WITH = 415;

    /**
     * VOLT
     */
    public const PHVOLT_RAW_BUFFER_SIZE = 256;

    public const PHVOLT_SCANNER_RETCODE_EOF        = -1;
    public const PHVOLT_SCANNER_RETCODE_ERR        = -2;
    public const PHVOLT_SCANNER_RETCODE_IMPOSSIBLE = -3;

    /** Modes */
    public const PHVOLT_MODE_RAW     = 0;
    public const PHVOLT_MODE_CODE    = 1;
    public const PHVOLT_MODE_COMMENT = 2;

    public const PHVOLT_T_IGNORE = 257;

    /* Literals & Identifiers */
    public const PHVOLT_T_INTEGER    = 258;
    public const PHVOLT_T_DOUBLE     = 259;
    public const PHVOLT_T_STRING     = 260;
    public const PHVOLT_T_NULL       = 261;
    public const PHVOLT_T_FALSE      = 262;
    public const PHVOLT_T_TRUE       = 263;
    public const PHVOLT_T_IDENTIFIER = 265;

    /* Operators */
    public const PHVOLT_T_ADD    = '+';
    public const PHVOLT_T_SUB    = '-';
    public const PHVOLT_T_MUL    = '*';
    public const PHVOLT_T_DIV    = '/';
    public const PHVOLT_T_MOD    = '%';
    public const PHVOLT_T_AND    = 266;
    public const PHVOLT_T_OR     = 267;
    public const PHVOLT_T_CONCAT = '~';
    public const PHVOLT_T_PIPE   = '|';

    public const PHVOLT_T_DOT   = '.';
    public const PHVOLT_T_COMMA = 269;

    public const PHVOLT_T_NOT          = '!';
    public const PHVOLT_T_LESS         = '<';
    public const PHVOLT_T_LESSEQUAL    = 270;
    public const PHVOLT_T_GREATER      = '>';
    public const PHVOLT_T_GREATEREQUAL = 271;
    public const PHVOLT_T_EQUALS       = 272;
    public const PHVOLT_T_NOTEQUALS    = 273;
    public const PHVOLT_T_IDENTICAL    = 274;
    public const PHVOLT_T_NOTIDENTICAL = 275;
    public const PHVOLT_T_RANGE        = 276;
    public const PHVOLT_T_ASSIGN       = '=';
    public const PHVOLT_T_COLON        = 277;
    public const PHVOLT_T_QUESTION     = '?';
    public const PHVOLT_T_POW          = 278;
    public const PHVOLT_T_INCR         = 279;
    public const PHVOLT_T_DECR         = 280;
    public const PHVOLT_T_ADD_ASSIGN   = 281;
    public const PHVOLT_T_SUB_ASSIGN   = 282;
    public const PHVOLT_T_MUL_ASSIGN   = 283;
    public const PHVOLT_T_DIV_ASSIGN   = 284;

    public const PHVOLT_T_PARENTHESES_OPEN  = '(';
    public const PHVOLT_T_PARENTHESES_CLOSE = ')';
    public const PHVOLT_T_SBRACKET_OPEN     = '[';
    public const PHVOLT_T_SBRACKET_CLOSE    = ']';
    public const PHVOLT_T_CBRACKET_OPEN     = '{';
    public const PHVOLT_T_CBRACKET_CLOSE    = '}';

    /** Reserved words */
    public const PHVOLT_T_IF            = 300;
    public const PHVOLT_T_ELSE          = 301;
    public const PHVOLT_T_ELSEIF        = 302;
    public const PHVOLT_T_ENDIF         = 303;
    public const PHVOLT_T_FOR           = 304;
    public const PHVOLT_T_ENDFOR        = 305;
    public const PHVOLT_T_SET           = 306;
    public const PHVOLT_T_BLOCK         = 307;
    public const PHVOLT_T_ENDBLOCK      = 308;
    public const PHVOLT_T_IN            = 309;
    public const PHVOLT_T_EXTENDS       = 310;
    public const PHVOLT_T_IS            = 311;
    public const PHVOLT_T_DEFINED       = 312;
    public const PHVOLT_T_INCLUDE       = 313;
    public const PHVOLT_T_CACHE         = 314;
    public const PHVOLT_T_ENDCACHE      = 315;
    public const PHVOLT_T_DO            = 316;
    public const PHVOLT_T_AUTOESCAPE    = 317;
    public const PHVOLT_T_ENDAUTOESCAPE = 318;
    public const PHVOLT_T_CONTINUE      = 319;
    public const PHVOLT_T_BREAK         = 320;
    public const PHVOLT_T_ELSEFOR       = 321;
    public const PHVOLT_T_MACRO         = 322;
    public const PHVOLT_T_ENDMACRO      = 323;
    public const PHVOLT_T_WITH          = 324;
    public const PHVOLT_T_CALL          = 325;
    public const PHVOLT_T_ENDCALL       = 326;
    public const PHVOLT_T_RETURN        = 327;

    /** Delimiters */
    public const PHVOLT_T_OPEN_DELIMITER   = 330;
    public const PHVOLT_T_CLOSE_DELIMITER  = 331;
    public const PHVOLT_T_OPEN_EDELIMITER  = 332;
    public const PHVOLT_T_CLOSE_EDELIMITER = 333;

    /** Special Tokens */
    public const PHVOLT_T_FCALL           = 350;
    public const PHVOLT_T_EXPR            = 354;
    public const PHVOLT_T_QUALIFIED       = 355;
    public const PHVOLT_T_ENCLOSED        = 356;
    public const PHVOLT_T_RAW_FRAGMENT    = 357;
    public const PHVOLT_T_EMPTY_STATEMENT = 358;
    public const PHVOLT_T_ECHO            = 359;
    public const PHVOLT_T_ARRAY           = 360;
    public const PHVOLT_T_ARRAYACCESS     = 361;
    public const PHVOLT_T_NOT_ISSET       = 362;
    public const PHVOLT_T_ISSET           = 363;
    public const PHVOLT_T_RESOLVED_EXPR   = 364;
    public const PHVOLT_T_SLICE           = 365;
    public const PHVOLT_T_TERNARY         = 366;
    public const PHVOLT_T_NOT_IN          = 367;

    public const PHVOLT_T_FILTER = 124;

    public const PHVOLT_T_MINUS = 368;
    public const PHVOLT_T_PLUS  = 369;

    public const PHVOLT_T_EMPTY    = 380;
    public const PHVOLT_T_EVEN     = 381;
    public const PHVOLT_T_ODD      = 382;
    public const PHVOLT_T_NUMERIC  = 383;
    public const PHVOLT_T_SCALAR   = 384;
    public const PHVOLT_T_ITERABLE = 385;

    public const PHVOLT_T_ISEMPTY    = 386;
    public const PHVOLT_T_ISEVEN     = 387;
    public const PHVOLT_T_ISODD      = 388;
    public const PHVOLT_T_ISNUMERIC  = 389;
    public const PHVOLT_T_ISSCALAR   = 390;
    public const PHVOLT_T_ISITERABLE = 391;

    public const PHVOLT_T_NOT_ISEMPTY    = 392;
    public const PHVOLT_T_NOT_ISEVEN     = 393;
    public const PHVOLT_T_NOT_ISODD      = 394;
    public const PHVOLT_T_NOT_ISNUMERIC  = 395;
    public const PHVOLT_T_NOT_ISSCALAR   = 396;
    public const PHVOLT_T_NOT_ISITERABLE = 397;

    public const PHVOLT_T_RAW    = 400;
    public const PHVOLT_T_ENDRAW = 401;

    /* switch/ -case statement */
    public const PHVOLT_T_SWITCH    = 411;
    public const PHVOLT_T_CASE      = 412;
    public const PHVOLT_T_DEFAULT   = 413;
    public const PHVOLT_T_ENDSWITCH = 414;
}
