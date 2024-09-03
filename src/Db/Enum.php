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

namespace Phalcon\Db;

use PDO;

/**
 * Constants for Phalcon\Db
 */
class Enum
{
    public const FETCH_ASSOC      = PDO::FETCH_ASSOC;
    public const FETCH_BOTH       = PDO::FETCH_BOTH;
    public const FETCH_BOUND      = PDO::FETCH_BOUND;
    public const FETCH_CLASS      = PDO::FETCH_CLASS;
    public const FETCH_CLASSTYPE  = PDO::FETCH_CLASSTYPE;
    public const FETCH_COLUMN     = PDO::FETCH_COLUMN;
    public const FETCH_DEFAULT    = PDO::FETCH_DEFAULT;
    public const FETCH_FUNC       = PDO::FETCH_FUNC;
    public const FETCH_GROUP      = PDO::FETCH_GROUP;
    public const FETCH_INTO       = PDO::FETCH_INTO;
    public const FETCH_KEY_PAIR   = PDO::FETCH_KEY_PAIR;
    public const FETCH_LAZY       = PDO::FETCH_LAZY;
    public const FETCH_NAMED      = PDO::FETCH_NAMED;
    public const FETCH_NUM        = PDO::FETCH_NUM;
    public const FETCH_OBJ        = PDO::FETCH_OBJ;
    public const FETCH_ORI_ABS    = PDO::FETCH_ORI_ABS;
    public const FETCH_ORI_FIRST  = PDO::FETCH_ORI_FIRST;
    public const FETCH_ORI_LAST   = PDO::FETCH_ORI_LAST;
    public const FETCH_ORI_NEXT   = PDO::FETCH_ORI_NEXT;
    public const FETCH_ORI_PRIOR  = PDO::FETCH_ORI_PRIOR;
    public const FETCH_ORI_REL    = PDO::FETCH_ORI_REL;
    public const FETCH_PROPS_LATE = PDO::FETCH_PROPS_LATE;
    public const FETCH_SERIALIZE  = PDO::FETCH_SERIALIZE;
    public const FETCH_UNIQUE     = PDO::FETCH_UNIQUE;
}
