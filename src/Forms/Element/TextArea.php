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

namespace Phalcon\Forms\Element;

/**
 * Component TEXTAREA for forms
 */
class TextArea extends AbstractElement
{
    /**
     * @var string
     */
    protected string $method = "inputTextarea";
}
