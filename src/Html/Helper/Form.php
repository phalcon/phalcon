<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Html\Exception;

/**
 * Class Form
 */
class Form extends AbstractHelper
{
    /**
     * Produce a <form> tag.
     *
     * @param array $attributes
     *
     * @return string
     * @throws Exception
     */
    public function __invoke(array $attributes = [])
    {
        $overrides = [
            "method"  => "post",
            "enctype" => "multipart/form-data",
        ];

        $overrides = $this->orderAttributes($overrides, $attributes);

        return $this->renderElement("form", $overrides);
    }
}
