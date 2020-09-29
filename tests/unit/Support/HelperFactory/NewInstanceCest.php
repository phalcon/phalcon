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

namespace Phalcon\Tests\Unit\Support\HelperFactory;

use Codeception\Example;
use Phalcon\Support\Arr\Blacklist;
use Phalcon\Support\Arr\Chunk;
use Phalcon\Support\Arr\First;
use Phalcon\Support\Arr\FirstKey;
use Phalcon\Support\Arr\Flatten;
use Phalcon\Support\Arr\Get;
use Phalcon\Support\Arr\Group;
use Phalcon\Support\Arr\Has;
use Phalcon\Support\Arr\IsUnique;
use Phalcon\Support\Arr\Last;
use Phalcon\Support\Arr\LastKey;
use Phalcon\Support\Arr\Order;
use Phalcon\Support\Arr\Pluck;
use Phalcon\Support\Arr\Set;
use Phalcon\Support\Arr\SliceLeft;
use Phalcon\Support\Arr\SliceRight;
use Phalcon\Support\Arr\Split;
use Phalcon\Support\Arr\ToObject;
use Phalcon\Support\Arr\ValidateAll;
use Phalcon\Support\Arr\ValidateAny;
use Phalcon\Support\Arr\Whitelist;
use Phalcon\Support\File\Basename;
use Phalcon\Support\HelperFactory;
use Phalcon\Support\Json\Decode;
use Phalcon\Support\Json\Encode;
use Phalcon\Support\Number\IsBetween;
use Phalcon\Support\Str\Concat;
use Phalcon\Support\Str\CountVowels;
use Phalcon\Support\Str\Decapitalize;
use Phalcon\Support\Str\Decrement;
use Phalcon\Support\Str\DirFromFile;
use Phalcon\Support\Str\DirSeparator;
use Phalcon\Support\Str\EndsWith;
use Phalcon\Support\Str\FirstBetween;
use Phalcon\Support\Str\Friendly;
use Phalcon\Support\Str\Humanize;
use Phalcon\Support\Str\Includes;
use Phalcon\Support\Str\Increment;
use Phalcon\Support\Str\IsAnagram;
use Phalcon\Support\Str\IsLower;
use Phalcon\Support\Str\IsPalindrome;
use Phalcon\Support\Str\IsUpper;
use Phalcon\Support\Str\Len;
use Phalcon\Support\Str\Lower;
use Phalcon\Support\Str\Random;
use Phalcon\Support\Str\ReduceSlashes;
use Phalcon\Support\Str\StartsWith;
use Phalcon\Support\Str\Ucwords;
use Phalcon\Support\Str\Underscore;
use Phalcon\Support\Str\Upper;
use UnitTester;

class NewInstanceCest
{
    /**
     * Tests Phalcon\Support :: newInstance()
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportHelperFactoryNewInstance(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\HelperFactory - newInstance() - ' . $example[0]);

        $factory = new HelperFactory();

        $expected = $example[1];
        $actual   = $factory->newInstance($example[0]);
        $I->assertInstanceOf($expected, $actual);
    }

    /**
     * @return \string[][]
     */
    private function getExamples(): array
    {
        return [
            [
                'blacklist',
                Blacklist::class,
            ],
            [
                'chunk',
                Chunk::class,
            ],
            [
                'first',
                First::class,
            ],
            [
                'firstKey',
                FirstKey::class,
            ],
            [
                'flatten',
                Flatten::class,
            ],
            [
                'get',
                Get::class,
            ],
            [
                'group',
                Group::class,
            ],
            [
                'has',
                Has::class,
            ],
            [
                'isUnique',
                IsUnique::class,
            ],
            [
                'last',
                Last::class,
            ],
            [
                'lastKey',
                LastKey::class,
            ],
            [
                'order',
                Order::class,
            ],
            [
                'pluck',
                Pluck::class,
            ],
            [
                'set',
                Set::class,
            ],
            [
                'sliceLeft',
                SliceLeft::class,
            ],
            [
                'sliceRight',
                SliceRight::class,
            ],
            [
                'split',
                Split::class,
            ],
            [
                'toObject',
                ToObject::class,
            ],
            [
                'validateAll',
                ValidateAll::class,
            ],
            [
                'validateAny',
                ValidateAny::class,
            ],
            [
                'whitelist',
                Whitelist::class,
            ],
            [
                'basename',
                Basename::class,
            ],
            [
                'decode',
                Decode::class,
            ],
            [
                'encode',
                Encode::class,
            ],
            [
                'between',
                IsBetween::class,
            ],
            [
                'concat',
                Concat::class,
            ],
            [
                'countVowels',
                CountVowels::class,
            ],
            [
                'decapitalize',
                Decapitalize::class,
            ],
            [
                'decrement',
                Decrement::class,
            ],
            [
                'dirFromFile',
                DirFromFile::class,
            ],
            [
                'dirSeparator',
                DirSeparator::class,
            ],
            [
                'endsWith',
                EndsWith::class,
            ],
            [
                'firstBetween',
                FirstBetween::class,
            ],
            [
                'friendly',
                Friendly::class,
            ],
            [
                'humanize',
                Humanize::class,
            ],
            [
                'includes',
                Includes::class,
            ],
            [
                'increment',
                Increment::class,
            ],
            [
                'isAnagram',
                IsAnagram::class,
            ],
            [
                'isLower',
                IsLower::class,
            ],
            [
                'isPalindrome',
                IsPalindrome::class,
            ],
            [
                'isUpper',
                IsUpper::class,
            ],
            [
                'len',
                Len::class,
            ],
            [
                'lower',
                Lower::class,
            ],
            [
                'random',
                Random::class,
            ],
            [
                'reduceSlashes',
                ReduceSlashes::class,
            ],
            [
                'startsWith',
                StartsWith::class,
            ],
            [
                'ucwords',
                Ucwords::class,
            ],
            [
                'underscore',
                Underscore::class,
            ],
            [
                'upper',
                Upper::class,
            ],
        ];
    }
}
