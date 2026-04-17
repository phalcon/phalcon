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
use Phalcon\Mvc\Model\ResultsetInterface;

use function count;
use function is_array;
use function is_object;
use function method_exists;

class ResultsetData implements SelectDataInterface
{
    public function __construct(
        protected ResultsetInterface $resultset,
        protected array $using
    ) {
        if (count($using) !== 2) {
            throw new InvalidArgumentException(
                "The 'using' parameter requires exactly two values"
            );
        }
    }

    public function getOptions(): array
    {
        [$usingZero, $usingOne] = $this->using;
        $options = [];

        foreach ($this->resultset as $option) {
            if (is_object($option)) {
                if (method_exists($option, 'readAttribute')) {
                    $optionValue = $option->readAttribute($usingZero);
                    $optionText  = $option->readAttribute($usingOne);
                } else {
                    $optionValue = $option->{$usingZero};
                    $optionText  = $option->{$usingOne};
                }
            } else {
                if (!is_array($option)) {
                    throw new InvalidArgumentException(
                        'Resultset returned an invalid value'
                    );
                }

                $optionValue = $option[$usingZero];
                $optionText  = $option[$usingOne];
            }

            $options[$optionValue] = $optionText;
        }

        return $options;
    }
}
