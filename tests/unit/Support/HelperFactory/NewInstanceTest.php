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

use Phalcon\Support\Exception;
use Phalcon\Support\Helper\Arr\Blacklist;
use Phalcon\Support\Helper\Arr\Chunk;
use Phalcon\Support\Helper\Arr\Filter;
use Phalcon\Support\Helper\Arr\First;
use Phalcon\Support\Helper\Arr\FirstKey;
use Phalcon\Support\Helper\Arr\Flatten;
use Phalcon\Support\Helper\Arr\Get;
use Phalcon\Support\Helper\Arr\Group;
use Phalcon\Support\Helper\Arr\Has;
use Phalcon\Support\Helper\Arr\IsUnique;
use Phalcon\Support\Helper\Arr\Last;
use Phalcon\Support\Helper\Arr\LastKey;
use Phalcon\Support\Helper\Arr\Order;
use Phalcon\Support\Helper\Arr\Pluck;
use Phalcon\Support\Helper\Arr\Set;
use Phalcon\Support\Helper\Arr\SliceLeft;
use Phalcon\Support\Helper\Arr\SliceRight;
use Phalcon\Support\Helper\Arr\Split;
use Phalcon\Support\Helper\Arr\ToObject;
use Phalcon\Support\Helper\Arr\ValidateAll;
use Phalcon\Support\Helper\Arr\ValidateAny;
use Phalcon\Support\Helper\Arr\Whitelist;
use Phalcon\Support\Helper\File\Basename;
use Phalcon\Support\Helper\Json\Decode;
use Phalcon\Support\Helper\Json\Encode;
use Phalcon\Support\Helper\Number\IsBetween;
use Phalcon\Support\Helper\Str\Camelize;
use Phalcon\Support\Helper\Str\Concat;
use Phalcon\Support\Helper\Str\CountVowels;
use Phalcon\Support\Helper\Str\Decapitalize;
use Phalcon\Support\Helper\Str\Decrement;
use Phalcon\Support\Helper\Str\DirFromFile;
use Phalcon\Support\Helper\Str\DirSeparator;
use Phalcon\Support\Helper\Str\Dynamic;
use Phalcon\Support\Helper\Str\EndsWith;
use Phalcon\Support\Helper\Str\FirstBetween;
use Phalcon\Support\Helper\Str\Friendly;
use Phalcon\Support\Helper\Str\Humanize;
use Phalcon\Support\Helper\Str\Includes;
use Phalcon\Support\Helper\Str\Increment;
use Phalcon\Support\Helper\Str\Interpolate;
use Phalcon\Support\Helper\Str\IsAnagram;
use Phalcon\Support\Helper\Str\IsLower;
use Phalcon\Support\Helper\Str\IsPalindrome;
use Phalcon\Support\Helper\Str\IsUpper;
use Phalcon\Support\Helper\Str\KebabCase;
use Phalcon\Support\Helper\Str\Len;
use Phalcon\Support\Helper\Str\Lower;
use Phalcon\Support\Helper\Str\PascalCase;
use Phalcon\Support\Helper\Str\Prefix;
use Phalcon\Support\Helper\Str\Random;
use Phalcon\Support\Helper\Str\ReduceSlashes;
use Phalcon\Support\Helper\Str\SnakeCase;
use Phalcon\Support\Helper\Str\StartsWith;
use Phalcon\Support\Helper\Str\Suffix;
use Phalcon\Support\Helper\Str\Ucwords;
use Phalcon\Support\Helper\Str\Uncamelize;
use Phalcon\Support\Helper\Str\Underscore;
use Phalcon\Support\Helper\Str\Upper;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\AbstractUnitTestCase;

final class NewInstanceTest extends AbstractUnitTestCase
{
    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        return [
            ["blacklist", Blacklist::class],
            ["chunk", Chunk::class],
            ["filter", Filter::class],
            ["first", First::class],
            ["firstKey", FirstKey::class],
            ["flatten", Flatten::class],
            ["get", Get::class],
            ["group", Group::class],
            ["has", Has::class],
            ["isUnique", IsUnique::class],
            ["last", Last::class],
            ["lastKey", LastKey::class],
            ["order", Order::class],
            ["pluck", Pluck::class],
            ["set", Set::class],
            ["sliceLeft", SliceLeft::class],
            ["sliceRight", SliceRight::class],
            ["split", Split::class],
            ["toObject", ToObject::class],
            ["validateAll", ValidateAll::class],
            ["validateAny", ValidateAny::class],
            ["whitelist", Whitelist::class],
            ["basename", Basename::class],
            ["decode", Decode::class],
            ["encode", Encode::class],
            ["isBetween", IsBetween::class],
            ["camelize", Camelize::class],
            ["concat", Concat::class],
            ["countVowels", CountVowels::class],
            ["decapitalize", Decapitalize::class],
            ["decrement", Decrement::class],
            ["dirFromFile", DirFromFile::class],
            ["dirSeparator", DirSeparator::class],
            ["dynamic", Dynamic::class],
            ["endsWith", EndsWith::class],
            ["firstBetween", FirstBetween::class],
            ["friendly", Friendly::class],
            ["humanize", Humanize::class],
            ["includes", Includes::class],
            ["increment", Increment::class],
            ["interpolate", Interpolate::class],
            ["isAnagram", IsAnagram::class],
            ["isLower", IsLower::class],
            ["isPalindrome", IsPalindrome::class],
            ["isUpper", IsUpper::class],
            ["kebabCase", KebabCase::class],
            ["len", Len::class],
            ["lower", Lower::class],
            ["prefix", Prefix::class],
            ["pascalCase", PascalCase::class],
            ["random", Random::class],
            ["reduceSlashes", ReduceSlashes::class],
            ["startsWith", StartsWith::class],
            ["snakeCase", SnakeCase::class],
            ["suffix", Suffix::class],
            ["ucwords", Ucwords::class],
            ["uncamelize", Uncamelize::class],
            ["underscore", Underscore::class],
            ["upper", Upper::class],
        ];
    }

    /**
     * Tests Phalcon\Support :: newInstance()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperFactoryNewInstance(
        string $method,
        string $className
    ): void {
        $factory = new HelperFactory();

        $expected = $className;
        $actual   = $factory->newInstance($method);
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * Tests Phalcon\Support :: newInstance()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperFactoryNewInstanceException(): void
    {
        $name = uniqid('service-');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service ' . $name . ' is not registered');

        $factory = new HelperFactory();
        $factory->newInstance($name);
    }
}
