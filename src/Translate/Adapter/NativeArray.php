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

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Exception;
use Phalcon\Translate\Exceptions\InvalidDataType;
use Phalcon\Translate\Exceptions\MissingContent;
use Phalcon\Translate\InterpolatorFactory;

use function is_array;

/**
 * Defines translation lists using PHP arrays
 *
 * @phpstan-type TOptions array{
 *      content?: array<string, string>,
 *      triggerError?: bool
 * }
 */
class NativeArray extends AbstractAdapter
{
    /**
     * @var array<string, string>
     */
    private array $translate = [];

    /**
     * NativeArray constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param TOptions            $options
     *
     * @throws InvalidDataType
     * @throws MissingContent
     */
    public function __construct(
        InterpolatorFactory $interpolator,
        array $options
    ) {
        parent::__construct($interpolator, $options);

        if (!isset($options['content'])) {
            throw new MissingContent();
        }

        if (!is_array($options['content'])) {
            throw new InvalidDataType();
        }

        $this->translate = $options['content'];
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param string $index
     *
     * @return bool
     */
    public function has(string $index): bool
    {
        return isset($this->translate[$index]);
    }

    /**
     * Returns the translation related to the given key
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     * @throws Exception
     */
    public function query(string $translateKey, array $placeholders = []): string
    {
        if (!isset($this->translate[$translateKey])) {
            return $this->notFound($translateKey);
        }

        return $this->replacePlaceholders(
            $this->translate[$translateKey],
            $placeholders
        );
    }

    /**
     * Returns the internal array
     *
     * @phpstan-return array<string, string>
     */
    public function toArray(): array
    {
        return $this->translate;
    }
}
