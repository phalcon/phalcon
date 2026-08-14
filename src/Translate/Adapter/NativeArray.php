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

use Phalcon\Contracts\Translate\TranslateTypes;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Exceptions\InvalidDataType;
use Phalcon\Translate\Exceptions\MissingContent;
use Phalcon\Translate\InterpolatorFactory;

use function is_array;

/**
 * Defines translation lists using PHP arrays
 *
 * @phpstan-import-type translate_array_options from TranslateTypes
 * @phpstan-import-type translate_data from TranslateTypes
 * @phpstan-import-type translate_placeholders from TranslateTypes
 */
class NativeArray extends AbstractAdapter
{
    /**
     * @phpstan-var translate_data
     */
    private array $translate = [];

    /**
     * NativeArray constructor.
     *
     * @phpstan-param translate_array_options $options
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

        /** @phpstan-var translate_data $content */
        $content = $options['content'];

        $this->translate = $content;
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @deprecated
     */
    public function exists(string $index): bool
    {
        return $this->has($index);
    }

    /**
     * Check whether is defined a translation key in the internal array
     */
    public function has(string $index): bool
    {
        return isset($this->translate[$index]);
    }

    /**
     * Returns the translation related to the given key
     *
     * @phpstan-param translate_placeholders $placeholders
     *
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
     * @phpstan-return translate_data
     */
    public function toArray(): array
    {
        return $this->translate;
    }
}
