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
 * Class Range
 *
 * @package Phalcon\Html\Tag\Input
 *
 * @property string $type
 */
class Range extends AbstractInput
{
    /**
     * @var string
     */
    protected string $type = 'range';
}
