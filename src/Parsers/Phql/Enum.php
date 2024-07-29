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

namespace Phalcon\Parsers\Phql;

/**
 * Constants
 */
class Enum
{
    /**
     * PHQL
     */
    public const PHQL_SCANNER_RETCODE_EOF        = -1;
    public const PHQL_SCANNER_RETCODE_ERR        = -2;
    public const PHQL_SCANNER_RETCODE_IMPOSSIBLE = -3;
    public const PHQL_T_ADD                      = '+';

    /* Literals & Identifiers */
    public const PHQL_T_AGAINST = 276;
    public const PHQL_T_ALL     = 338;
    public const PHQL_T_AND     = 266;
    public const PHQL_T_AS      = 305;
    public const PHQL_T_ASC     = 327;

    /* Operators */
    public const PHQL_T_BETWEEN      = 331;
    public const PHQL_T_BETWEEN_NOT  = 332;
    public const PHQL_T_BITWISE_AND  = '&';
    public const PHQL_T_BITWISE_NOT  = '~';
    public const PHQL_T_BITWISE_OR   = '|';
    public const PHQL_T_BITWISE_XOR  = '^';
    public const PHQL_T_BPLACEHOLDER = 277;
    public const PHQL_T_BY           = 311;
    public const PHQL_T_CASE         = 409;
    public const PHQL_T_CAST         = 333;
    public const PHQL_T_COLON        = ':';
    public const PHQL_T_COMMA        = 269;
    public const PHQL_T_CONVERT      = 336;
    public const PHQL_T_CROSS        = 324;
    public const PHQL_T_CROSSJOIN    = 363;
    public const PHQL_T_DELETE       = 303;
    public const PHQL_T_DESC         = 328;
    public const PHQL_T_DISTINCT     = 330;
    public const PHQL_T_DIV          = '/';
    public const PHQL_T_DOMAINALL    = 353;
    public const PHQL_T_DOT          = '.';
    public const PHQL_T_DOUBLE       = 259;
    public const PHQL_T_ELSE         = 411;
    public const PHQL_T_ENCLOSED     = 356;
    public const PHQL_T_END          = 412;
    public const PHQL_T_EQUALS       = '=';
    public const PHQL_T_EXISTS       = 408;
    public const PHQL_T_EXPR         = 354;
    public const PHQL_T_FALSE        = 335;
    /** Special Tokens */
    public const PHQL_T_FCALL        = 350;
    public const PHQL_T_FOR          = 339;
    public const PHQL_T_FROM         = 304;
    public const PHQL_T_FULL         = 325;
    public const PHQL_T_FULLJOIN     = 364;
    public const PHQL_T_GREATER      = '>';
    public const PHQL_T_GREATEREQUAL = 272;
    public const PHQL_T_GROUP        = 313;
    public const PHQL_T_HAVING       = 314;
    public const PHQL_T_HINTEGER     = 414;
    public const PHQL_T_IDENTIFIER   = 265;
    public const PHQL_T_IGNORE       = 257;
    public const PHQL_T_ILIKE        = 275;
    public const PHQL_T_IN           = 315;
    public const PHQL_T_INNER        = 317;
    public const PHQL_T_INNERJOIN    = 360;
    public const PHQL_T_INSERT       = 306;
    public const PHQL_T_INTEGER      = 258;
    public const PHQL_T_INTO         = 307;
    public const PHQL_T_IS           = 321;
    public const PHQL_T_ISNOTNULL    = 366;
    public const PHQL_T_ISNULL       = 365;
    public const PHQL_T_JOIN         = 318;
    public const PHQL_T_LEFT         = 319;
    public const PHQL_T_LEFTJOIN     = 361;
    public const PHQL_T_LESS         = '<';
    public const PHQL_T_LESSEQUAL    = 271;
    public const PHQL_T_LIKE         = 268;
    public const PHQL_T_LIMIT        = 312;
    public const PHQL_T_MINUS        = 367;
    public const PHQL_T_MOD          = '%';
    public const PHQL_T_MUL          = '*';
    public const PHQL_T_NILIKE       = 357;
    public const PHQL_T_NLIKE        = 351;
    public const PHQL_T_NOT          = '!';
    public const PHQL_T_NOTEQUALS    = 270;
    public const PHQL_T_NOTIN        = 323;
    /** Placeholders */
    public const PHQL_T_NPLACEHOLDER        = 273;
    public const PHQL_T_NULL                = 322;
    public const PHQL_T_OFFSET              = 329;
    public const PHQL_T_ON                  = 316;
    public const PHQL_T_OR                  = 267;
    public const PHQL_T_ORDER               = 310;
    public const PHQL_T_OUTER               = 326;
    public const PHQL_T_PARENTHESES_CLOSE   = ')';
    public const PHQL_T_PARENTHESES_OPEN    = '(';
    public const PHQL_T_QUALIFIED           = 355;
    public const PHQL_T_RAW_QUALIFIED       = 358;
    public const PHQL_T_RIGHT               = 320;
    public const PHQL_T_RIGHTJOIN           = 362;
    public const PHQL_T_SELECT              = 309;
    public const PHQL_T_SET                 = 301;
    public const PHQL_T_SPLACEHOLDER        = 274;
    public const PHQL_T_STARALL             = 352;
    public const PHQL_T_STRING              = 260;
    public const PHQL_T_SUB                 = '-';
    public const PHQL_T_SUBQUERY            = 407;
    public const PHQL_T_THEN                = 413;
    public const PHQL_T_TRUE                = 334;
    public const PHQL_T_TS_AND              = 403;
    public const PHQL_T_TS_CONTAINS_ANOTHER = 405;
    public const PHQL_T_TS_CONTAINS_IN      = 406;
    /** Postgresql Text Search Operators */
    public const PHQL_T_TS_MATCHES = 401;
    public const PHQL_T_TS_NEGATE  = 404;
    public const PHQL_T_TS_OR      = 402;
    /** Reserved words */
    public const PHQL_T_UPDATE = 300;
    public const PHQL_T_USING  = 337;
    public const PHQL_T_VALUES = 308;
    public const PHQL_T_WHEN   = 410;
    public const PHQL_T_WHERE  = 302;
    public const PHQL_T_WITH   = 415;
}
