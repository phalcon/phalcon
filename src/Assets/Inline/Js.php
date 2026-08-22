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

namespace Phalcon\Assets\Inline;

use Phalcon\Assets\Inline as InlineBase;
use Phalcon\Contracts\Assets\AssetsTypes;

/**
 * Represents an inline JavaScript
 *
 * @phpstan-import-type assets_attributes from AssetsTypes
 */
class Js extends InlineBase
{
    /**
     * Js constructor.
     *
     * @param assets_attributes $attributes
     */
    public function __construct(
        string $content,
        bool $filter = true,
        array $attributes = []
    ) {
        if (empty($attributes)) {
            $attributes = [
                'type' => 'application/javascript',
            ];
        }

        parent::__construct('js', $content, $filter, $attributes);
    }
}
