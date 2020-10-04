<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support;

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
use Phalcon\Support\Traits\FactoryTrait;

use function call_user_func_array;

/**
 * This class offers quick string functions throughout the framework
 *
 * @method blacklist(array $collection, array $blackList): array
 * @method chunk(array $collection, int $size, bool $preserveKeys = false): array
 * @method first(array $collection, callable $method = null)
 * @method firstKey(array $collection, callable $method = null)
 * @method flatten(array $collection, bool $deep = false): array
 * @method get(array $collection, $index, $defaultValue = null, string $cast = null)
 * @method group(array $collection, $method): array
 * @method has(array $collection, $index): bool
 * @method isUnique(array $collection): bool
 * @method last(array $collection, callable $method = null)
 * @method lastKey(array $collection, callable $method = null)
 * @method order(array $collection, $attribute, string $order = 'asc'): array
 * @method pluck(array $collection, string $element): array
 * @method set(array $collection, $value, $index = null): array
 * @method sliceLeft(array $collection, int $elements = 1): array
 * @method sliceRight(array $collection, int $elements = 1): array
 * @method split(array $collection): array
 * @method toObject(array $collection): object
 * @method validateAll(array $collection, callable $method): bool
 * @method validateAny(array $collection, callable $method): bool
 * @method whitelist(array $collection, array $whiteList): array
 * @method basename(string $uri, string $suffix = null): string
 * @method decode(string $data, bool $associative = false, int $depth = 512, int $options = 0)
 * @method encode($data, int $options = 0, int $depth = 512): string
 * @method between(int $value, int $start, int $end): bool
 * @method concat(string $delimiter, string $first, string $second, string ...$arguments): string
 * @method countVowels(string $text): int
 * @method decapitalize(string $text, bool $upperRest = false, string $encoding = 'UTF-8'): string
 * @method decrement(string $text, string $separator = '_'): string
 * @method dirFromFile(string $file): string
 * @method dirSeparator(string $directory): string
 * @method endsWith(string $haystack, string $needle, bool $ignoreCase = true): bool
 * @method firstBetween(string $text, string $start, string $end): string
 * @method friendly(string $text, string $separator = '-', bool $lowercase = true, $replace = null): string
 * @method humanize(string $text): string
 * @method includes(string $haystack, string $needle): bool
 * @method increment(string $text, string $separator = '_'): string
 * @method isAnagram(string $first, string $second): bool
 * @method isLower(string $text, string $encoding = 'UTF-8'): bool
 * @method isPalindrome(string $text): bool
 * @method isUpper(string $text, string $encoding = 'UTF-8'): bool
 * @method len(string $text, string $encoding = 'UTF-8'): int
 * @method lower(string $text, string $encoding = 'UTF-8'): string
 * @method random(int $type = 0, int $length = 8): string
 * @method reduceSlashes(string $text): string
 * @method startsWith(string $haystack, string $needle, bool $ignoreCase = true): bool
 * @method ucwords(string $text, string $encoding = 'UTF-8'): string
 * @method underscore(string $text): string
 * @method upper(string $text, string $encoding = 'UTF-8'): string
 */
class HelperFactory
{
    use FactoryTrait;

    /**
     * FactoryTrait constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $arguments)
    {
        $helper = $this->newInstance($name);

        return call_user_func_array([$helper, '__invoke'], $arguments);
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function newInstance(string $name)
    {
        $definition = $this->getService($name);

        return new $definition();
    }

    /**
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'blacklist'     => Blacklist::class,
            'chunk'         => Chunk::class,
            'first'         => First::class,
            'firstKey'      => FirstKey::class,
            'flatten'       => Flatten::class,
            'get'           => Get::class,
            'group'         => Group::class,
            'has'           => Has::class,
            'isUnique'      => IsUnique::class,
            'last'          => Last::class,
            'lastKey'       => LastKey::class,
            'order'         => Order::class,
            'pluck'         => Pluck::class,
            'set'           => Set::class,
            'sliceLeft'     => SliceLeft::class,
            'sliceRight'    => SliceRight::class,
            'split'         => Split::class,
            'toObject'      => ToObject::class,
            'validateAll'   => ValidateAll::class,
            'validateAny'   => ValidateAny::class,
            'whitelist'     => Whitelist::class,
            'basename'      => Basename::class,
            'decode'        => Decode::class,
            'encode'        => Encode::class,
            'between'       => IsBetween::class,
            'concat'        => Concat::class,
            'countVowels'   => CountVowels::class,
            'decapitalize'  => Decapitalize::class,
            'decrement'     => Decrement::class,
            'dirFromFile'   => DirFromFile::class,
            'dirSeparator'  => DirSeparator::class,
            'endsWith'      => EndsWith::class,
            'firstBetween'  => FirstBetween::class,
            'friendly'      => Friendly::class,
            'humanize'      => Humanize::class,
            'includes'      => Includes::class,
            'increment'     => Increment::class,
            'isAnagram'     => IsAnagram::class,
            'isLower'       => IsLower::class,
            'isPalindrome'  => IsPalindrome::class,
            'isUpper'       => IsUpper::class,
            'len'           => Len::class,
            'lower'         => Lower::class,
            'random'        => Random::class,
            'reduceSlashes' => ReduceSlashes::class,
            'startsWith'    => StartsWith::class,
            'ucwords'       => Ucwords::class,
            'underscore'    => Underscore::class,
            'upper'         => Upper::class,
        ];
    }
}
