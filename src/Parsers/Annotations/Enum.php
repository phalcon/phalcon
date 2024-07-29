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

namespace Phalcon\Parsers\Annotations;

/**
 * Constants
 */
class Enum
{
    public const PHANNOT_MODE_ANNOTATION = 1;
    /** Modes */
    public const PHANNOT_MODE_RAW = 0;
    /**
     * ANNOTATIONS
     */
    public const PHANNOT_SCANNER_RETCODE_EOF        = -1;
    public const PHANNOT_SCANNER_RETCODE_ERR        = -2;
    public const PHANNOT_SCANNER_RETCODE_IMPOSSIBLE = -3;
    public const PHANNOT_T_ANNOTATION               = 300;
    public const PHANNOT_T_ARBITRARY_TEXT           = 309;
    public const PHANNOT_T_ARRAY                    = 308;

    /* Literals & Identifiers */
    public const PHANNOT_T_AT                  = "@";
    public const PHANNOT_T_BRACKET_CLOSE       = "}";
    public const PHANNOT_T_BRACKET_OPEN        = "{";
    public const PHANNOT_T_COLON               = ":";
    public const PHANNOT_T_COMMA               = ",";
    public const PHANNOT_T_DOCBLOCK_ANNOTATION = 299;
    public const PHANNOT_T_DOT                 = ".";
    public const PHANNOT_T_DOUBLE              = 302;
    public const PHANNOT_T_EQUALS              = "=";

    /* Operators */
    public const PHANNOT_T_FALSE             = 305;
    public const PHANNOT_T_IDENTIFIER        = 307;
    public const PHANNOT_T_IGNORE            = 297;
    public const PHANNOT_T_INTEGER           = 301;
    public const PHANNOT_T_NULL              = 304;
    public const PHANNOT_T_PARENTHESES_CLOSE = ")";
    public const PHANNOT_T_PARENTHESES_OPEN  = "(";
    public const PHANNOT_T_SBRACKET_CLOSE    = "]";
    public const PHANNOT_T_SBRACKET_OPEN     = "[";
    public const PHANNOT_T_STRING            = 303;
    public const PHANNOT_T_TRUE              = 306;
}
