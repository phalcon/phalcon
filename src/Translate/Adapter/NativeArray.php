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
use Phalcon\Translate\InterpolatorFactory;

use function is_array;

/**
 * Defines translation lists using PHP arrays
 *
 * @phpstan-type TOptions = array{
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
     * @var bool
     */
    private bool $triggerError = false;

    /**
     * NativeArray constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param TOptions            $options
     *
     * @throws Exception
     */
    public function __construct(
        InterpolatorFactory $interpolator,
        array $options
    ) {
        parent::__construct($interpolator, $options);

        if (!isset($options['content'])) {
            throw new Exception('Translation content was not provided');
        }

        if (!is_array($options['content'])) {
            throw new Exception('Translation data must be an array');
        }

        $this->triggerError = (bool)($options['triggerError'] ?? false);
        $this->translate    = $options['content'];
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
     * Whenever a key is not found this method will be called
     *
     * @param string $index
     *
     * @return string
     * @throws Exception
     */
    public function notFound(string $index): string
    {
        if (true === $this->triggerError) {
            throw new Exception('Cannot find translation key: ' . $index);
        }

        return $index;
    }

    /**
     * Returns the translation related to the given key
     *
     * @param string                $translateKey
     * @param array<string, string> $placeholders
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
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->translate;
    }
}
