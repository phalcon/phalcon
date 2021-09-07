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

use Phalcon\Support\Helper\Arr\Blacklist;
use Phalcon\Support\Helper\Arr\Chunk;
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
use Phalcon\Support\Helper\Str\Concat;
use Phalcon\Support\Helper\Str\CountVowels;
use Phalcon\Support\Helper\Str\Decapitalize;
use Phalcon\Support\Helper\Str\Decrement;
use Phalcon\Support\Helper\Str\DirFromFile;
use Phalcon\Support\Helper\Str\DirSeparator;
use Phalcon\Support\Helper\Str\EndsWith;
use Phalcon\Support\Helper\Str\FirstBetween;
use Phalcon\Support\Helper\Str\Friendly;
use Phalcon\Support\Helper\Str\Humanize;
use Phalcon\Support\Helper\Str\Includes;
use Phalcon\Support\Helper\Str\Increment;
use Phalcon\Support\Helper\Str\IsAnagram;
use Phalcon\Support\Helper\Str\IsLower;
use Phalcon\Support\Helper\Str\IsPalindrome;
use Phalcon\Support\Helper\Str\IsUpper;
use Phalcon\Support\Helper\Str\Len;
use Phalcon\Support\Helper\Str\Lower;
use Phalcon\Support\Helper\Str\Prefix;
use Phalcon\Support\Helper\Str\Random;
use Phalcon\Support\Helper\Str\ReduceSlashes;
use Phalcon\Support\Helper\Str\StartsWith;
use Phalcon\Support\Helper\Str\Suffix;
use Phalcon\Support\Helper\Str\Ucwords;
use Phalcon\Support\Helper\Str\Underscore;
use Phalcon\Support\Helper\Str\Upper;
use Phalcon\Support\Traits\FactoryTrait;

use function call_user_func_array;

/**
 * This class offers quick string functions throughout the framework
 *
 * @method array  blacklist(array $collection, array $blackList)
 * @method array  chunk(array $collection, int $size, bool $preserveKeys = false)
 * @method mixed  first(array $collection, callable $method = null)
 * @method mixed  firstKey(array $collection, callable $method = null)
 * @method array  flatten(array $collection, bool $deep = false)
 * @method mixed  get(array $collection, $index, $defaultValue = null, string $cast = null)
 * @method array  group(array $collection, $method)
 * @method bool   has(array $collection, $index)
 * @method bool   isUnique(array $collection)
 * @method mixed  last(array $collection, callable $method = null)
 * @method mixed  lastKey(array $collection, callable $method = null)
 * @method array  order(array $collection, $attribute, string $order = 'asc')
 * @method array  pluck(array $collection, string $element)
 * @method array  set(array $collection, $value, $index = null)
 * @method array  sliceLeft(array $collection, int $elements = 1)
 * @method array  sliceRight(array $collection, int $elements = 1)
 * @method array  split(array $collection)
 * @method object toObject(array $collection)
 * @method bool   validateAll(array $collection, callable $method)
 * @method bool   validateAny(array $collection, callable $method)
 * @method array  whitelist(array $collection, array $whiteList)
 * @method string basename(string $uri, string $suffix = null)
 * @method string decode(string $data, bool $associative = false, int $depth = 512, int $options = 0)
 * @method string encode($data, int $options = 0, int $depth = 512)
 * @method bool   between(int $value, int $start, int $end)
 * @method string concat(string $delimiter, string $first, string $second, string ...$arguments)
 * @method int    countVowels(string $text)
 * @method string decapitalize(string $text, bool $upperRest = false, string $encoding = 'UTF-8')
 * @method string decrement(string $text, string $separator = '_')
 * @method string dirFromFile(string $file)
 * @method string dirSeparator(string $directory)
 * @method bool   endsWith(string $haystack, string $needle, bool $ignoreCase = true)
 * @method string firstBetween(string $text, string $start, string $end)
 * @method string friendly(string $text, string $separator = '-', bool $lowercase = true, $replace = null)
 * @method string humanize(string $text)
 * @method bool   includes(string $haystack, string $needle)
 * @method string increment(string $text, string $separator = '_')
 * @method bool   isAnagram(string $first, string $second)
 * @method bool   isLower(string $text, string $encoding = 'UTF-8')
 * @method bool   isPalindrome(string $text)
 * @method bool   isUpper(string $text, string $encoding = 'UTF-8')
 * @method int    len(string $text, string $encoding = 'UTF-8')
 * @method string lower(string $text, string $encoding = 'UTF-8')
 * @method string prefix($text, string $prefix)
 * @method string random(int $type = 0, int $length = 8)
 * @method string reduceSlashes(string $text)
 * @method bool   startsWith(string $haystack, string $needle, bool $ignoreCase = true)
 * @method string suffix($text, string $suffix)
 * @method string ucwords(string $text, string $encoding = 'UTF-8')
 * @method string underscore(string $text)
 * @method string upper(string $text, string $encoding = 'UTF-8')
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
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
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
            'prefix'        => Prefix::class,
            'random'        => Random::class,
            'reduceSlashes' => ReduceSlashes::class,
            'startsWith'    => StartsWith::class,
            'suffix'        => Suffix::class,
            'ucwords'       => Ucwords::class,
            'underscore'    => Underscore::class,
            'upper'         => Upper::class,
        ];
    }
}
