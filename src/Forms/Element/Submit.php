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
 * Component INPUT[type=submit] for forms
 */
class Submit extends AbstractElement
{
    /**
     * @var string
     */
    protected string $method = "inputSubmit";
}
