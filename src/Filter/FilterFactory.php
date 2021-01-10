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

namespace Phiz\Filter;

use Phiz\Filter\Sanitize\AbsInt;
use Phiz\Filter\Sanitize\Alnum;
use Phiz\Filter\Sanitize\Alpha;
use Phiz\Filter\Sanitize\BoolVal;
use Phiz\Filter\Sanitize\Email;
use Phiz\Filter\Sanitize\FloatVal;
use Phiz\Filter\Sanitize\IntVal;
use Phiz\Filter\Sanitize\Lower;
use Phiz\Filter\Sanitize\LowerFirst;
use Phiz\Filter\Sanitize\Regex;
use Phiz\Filter\Sanitize\Remove;
use Phiz\Filter\Sanitize\Replace;
use Phiz\Filter\Sanitize\Special;
use Phiz\Filter\Sanitize\SpecialFull;
use Phiz\Filter\Sanitize\StringVal;
use Phiz\Filter\Sanitize\Striptags;
use Phiz\Filter\Sanitize\Trim;
use Phiz\Filter\Sanitize\Upper;
use Phiz\Filter\Sanitize\UpperFirst;
use Phiz\Filter\Sanitize\UpperWords;
use Phiz\Filter\Sanitize\Url;

/**
 * Class FilterFactory
 *
 * @package Phiz\Filter
 */
class FilterFactory
{
    /**
     * Returns a Locator object with all the helpers defined in anonymous
     * functions
     */
    public function newInstance(): FilterInterface
    {
        return new Filter($this->getAdapters());
    }

    protected function getAdapters(): array
    {
        return [
            Filter::FILTER_ABSINT      => AbsInt::class,
            Filter::FILTER_ALNUM       => Alnum::class,
            Filter::FILTER_ALPHA       => Alpha::class,
            Filter::FILTER_BOOL        => BoolVal::class,
            Filter::FILTER_EMAIL       => Email::class,
            Filter::FILTER_FLOAT       => FloatVal::class,
            Filter::FILTER_INT         => IntVal::class,
            Filter::FILTER_LOWER       => Lower::class,
            Filter::FILTER_LOWERFIRST  => LowerFirst::class,
            Filter::FILTER_REGEX       => Regex::class,
            Filter::FILTER_REMOVE      => Remove::class,
            Filter::FILTER_REPLACE     => Replace::class,
            Filter::FILTER_SPECIAL     => Special::class,
            Filter::FILTER_SPECIALFULL => SpecialFull::class,
            Filter::FILTER_STRING      => StringVal::class,
            Filter::FILTER_STRIPTAGS   => Striptags::class,
            Filter::FILTER_TRIM        => Trim::class,
            Filter::FILTER_UPPER       => Upper::class,
            Filter::FILTER_UPPERFIRST  => UpperFirst::class,
            Filter::FILTER_UPPERWORDS  => UpperWords::class,
            Filter::FILTER_URL         => Url::class,
        ];
    }
}
