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

namespace Phalcon\Tests\Fixtures\Traits;

use IntegrationTester;
use Phalcon\Filter\Validation\Validator\Alnum;
use Phalcon\Filter\Validation\Validator\Alpha;
use Phalcon\Filter\Validation\Validator\Between;
use Phalcon\Filter\Validation\Validator\Callback;
use Phalcon\Filter\Validation\Validator\Confirmation;
use Phalcon\Filter\Validation\Validator\CreditCard;
use Phalcon\Filter\Validation\Validator\Date;
use Phalcon\Filter\Validation\Validator\Digit;
use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Validator\ExclusionIn;
use Phalcon\Filter\Validation\Validator\File;
use Phalcon\Filter\Validation\Validator\Identical;
use Phalcon\Filter\Validation\Validator\InclusionIn;
use Phalcon\Filter\Validation\Validator\Ip;
use Phalcon\Filter\Validation\Validator\Numericality;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Filter\Validation\Validator\Uniqueness;
use Phalcon\Filter\Validation\Validator\Url;
use Phalcon\Filter\Validation\ValidatorInterface;

trait ValidationTrait
{
    /**
     * @return \class-string[][]
     */
    public static function getClasses(): array
    {
        return [
            [File\Resolution\Equal::class],
            [File\Resolution\Max::class],
            [File\Resolution\Min::class],
            [File\Size\Equal::class],
            [File\Size\Max::class],
            [File\Size\Min::class],
            [StringLength\Max::class],
            [StringLength\Min::class],
            [Alnum::class],
            [Alpha::class],
            [Between::class],
            [Callback::class],
            [Confirmation::class],
            [CreditCard::class],
            [Date::class],
            [Digit::class],
            [Email::class],
            [ExclusionIn::class],
            [File::class],
            [Identical::class],
            [InclusionIn::class],
            [Ip::class],
            [Numericality::class],
            [PresenceOf::class],
            [Regex::class],
            [StringLength::class],
            [Uniqueness::class],
            [Url::class],
        ];
    }
}
