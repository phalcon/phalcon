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

namespace Phalcon\Translate\Interpolator;

use function vsprintf;

class IndexedArray implements InterpolatorInterface
{
    /**
     * Replaces placeholders by the values passed
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     */
    public function replacePlaceholders(
        string $translation,
        array $placeholders = []
    ): string {
        if (!empty($placeholders)) {
            return vsprintf($translation, $placeholders);
        }

        return $translation;
    }
}
