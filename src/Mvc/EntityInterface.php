<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc;

/**
 * Phiz\Mvc\EntityInterface
 *
 * Interface for Phiz\Mvc\Collection and Phiz\Mvc\Model
 */
interface EntityInterface
{
    /**
     * Reads an attribute value by its name
     */
    public function readAttribute(string $attribute): mixed;

    /**
     * Writes an attribute value by its name
     */
    public function writeAttribute(string $attribute, $value): void;
}
