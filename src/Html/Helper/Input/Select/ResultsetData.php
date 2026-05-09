<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input\Select;

use InvalidArgumentException;
use Phalcon\Contracts\Html\Helper\Input\SelectData;
use Phalcon\Mvc\Model\ResultsetInterface;

use function call_user_func;
use function count;
use function is_callable;
use function method_exists;

class ResultsetData implements SelectData
{
    /**
     * @var array|null
     */
    protected ?array $resolvedAttributes = null;

    /**
     * @var array|null
     */
    protected ?array $resolvedOptions = null;

    /**
     * @param ResultsetInterface $resultset
     * @param array              $using
     * @param array              $attributesMap
     */
    public function __construct(
        protected ResultsetInterface $resultset,
        protected array $using,
        protected array $attributesMap = []
    ) {
        if (count($using) !== 2) {
            throw new InvalidArgumentException(
                "The 'using' parameter requires exactly two values"
            );
        }
    }

    /**
     * Returns per-option attribute maps, keyed by option value.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        if (null === $this->resolvedAttributes) {
            $this->resolve();
        }

        return $this->resolvedAttributes;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        if (null === $this->resolvedOptions) {
            $this->resolve();
        }

        return $this->resolvedOptions;
    }

    /**
     * Reads a property from the row, supporting both objects (via
     * `readAttribute` when available) and plain arrays.
     *
     * @param mixed  $option
     * @param string $field
     *
     * @return mixed
     */
    protected function readField(mixed $option, string $field): mixed
    {
        if (is_object($option)) {
            if (method_exists($option, 'readAttribute')) {
                return $option->readAttribute($field);
            }

            return $option->{$field};
        }

        return $option[$field];
    }

    /**
     * Walks the resultset once, building both the option map and the
     * per-option resolved attribute map. Closures in `attributesMap`
     * receive the current row; static values are passed through.
     * `false` or `null` values skip the attribute entirely.
     */
    protected function resolve(): void
    {
        [$usingZero, $usingOne] = $this->using;

        $options = [];
        $attrs   = [];

        foreach ($this->resultset as $option) {
            if (!is_object($option) && !is_array($option)) {
                throw new InvalidArgumentException(
                    'Resultset returned an invalid value'
                );
            }

            $optionValue = $this->readField($option, $usingZero);
            $optionText  = $this->readField($option, $usingOne);

            $options[$optionValue] = $optionText;

            if (!empty($this->attributesMap)) {
                $optionAttrs = [];

                foreach ($this->attributesMap as $attrName => $attrSpec) {
                    $attrValue = is_callable($attrSpec)
                        ? call_user_func($attrSpec, $option)
                        : $attrSpec;

                    if (false !== $attrValue && null !== $attrValue) {
                        $optionAttrs[$attrName] = (string) $attrValue;
                    }
                }

                if (!empty($optionAttrs)) {
                    $attrs[$optionValue] = $optionAttrs;
                }
            }
        }

        $this->resolvedOptions    = $options;
        $this->resolvedAttributes = $attrs;
    }
}
