<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

/**
 * Class Input
 *
 * @package Phalcon\Html\Helper\Input
 */
class Input extends AbstractInput
{
    /**
     * Sets the type of the input
     *
     * @param string $type
     *
     * @return AbstractInput
     */
    public function setType(string $type): AbstractInput
    {
        $this->type = $type;

        return $this;
    }
}
