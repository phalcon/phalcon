<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Tag;

use Phalcon\Html\Exception;

/**
 * Class Label
 *
 * @package Phalcon\Html\Tag
 */
class Label extends AbstractHelper
{
    /**
     * Produce a `<label>` tag.
     *
     * @param array $attributes
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(array $attributes = [])
    {
        return $this->renderElement('label', $attributes);
    }
}
