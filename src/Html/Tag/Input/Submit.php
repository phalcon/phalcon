<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Tag\Input;

/**
 * Class Submit
 *
 * @package Phalcon\Html\Tag\Input
 *
 * @property string $type
 */
class Submit extends AbstractInput
{
    /**
     * @var string
     */
    protected string $type = 'submit';
}
