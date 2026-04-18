<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input\Select;

/**
 * Wraps a plain PHP array as a SELECT data provider.
 *
 * Keys are option values; string values are labels;
 * array values define optgroups.
 */
class ArrayData implements SelectDataInterface
{
    /**
     * @param array $data
     */
    public function __construct(
        protected array $data = []
    ) {
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->data;
    }
}
