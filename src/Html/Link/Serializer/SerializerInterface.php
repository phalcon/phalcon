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

namespace Phalcon\Html\Link\Serializer;

/**
 * Class Phalcon\Http\Link\Serializer\SerializerInterface
 */
interface SerializerInterface
{
    /**
     * Serializer method
     *
     * @param array $links
     *
     * @return string|null
     */
    public function serialize(array $links): string | null;
}
