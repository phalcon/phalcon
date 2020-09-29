<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support;

use Phalcon\Support\Str\Concat;
use Phalcon\Support\Str\CountVowels;
use Phalcon\Support\Str\DirFromFile;
use Phalcon\Support\Str\DirSeparator;
use Phalcon\Support\Str\FirstBetween;
use Phalcon\Support\Str\Includes;
use Phalcon\Support\Str\Len;
use Phalcon\Support\Str\Random;
use Phalcon\Support\Traits\FactoryTrait;

use function call_user_func_array;

/**
 * This class offers quick string functions throughout the framework
 *
 * @method concat(string $delimiter, string $first, string $second, string ...$arguments): string
 * @method countVowels(string $data): string
 */
class HelperFactory
{
    use FactoryTrait;

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

        return call_user_func_array([$helper, $name], $arguments);
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
    protected function getAdapters(): array
    {
        return [
            'concat'       => Concat::class,
            'countVowels'  => CountVowels::class,
            'dirFromFile'  => DirFromFile::class,
            'dirSeparator' => DirSeparator::class,
            'firstBetween' => FirstBetween::class,
            'includes'     => Includes::class,
            'len'          => Len::class,
            'random'       => Random::class,
        ];
    }
}
