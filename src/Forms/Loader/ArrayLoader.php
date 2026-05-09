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

namespace Phalcon\Forms\Loader;

use Phalcon\Contracts\Forms\Schema;
use Phalcon\Forms\Exception;

/**
 * Supplies form element definitions from a PHP array.
 */
class ArrayLoader implements Schema
{
    /**
     * @param array<int, array<string, mixed>> $definitions
     */
    public function __construct(
        private readonly array $definitions
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public function load(): array
    {
        foreach ($this->definitions as $index => $definition) {
            $this->validateDefinition($definition, $index);
        }

        return $this->definitions;
    }

    /**
     * @param mixed $definition
     * @param int   $index
     *
     * @throws Exception
     */
    private function validateDefinition(mixed $definition, int $index): void
    {
        if (!is_array($definition)) {
            throw new Exception(
                'Form schema definition at index ' . $index . ' must be an array'
            );
        }

        if (empty($definition['type'])) {
            throw new Exception(
                'Form schema definition at index ' . $index . ' is missing required key "type"'
            );
        }

        if (empty($definition['name'])) {
            throw new Exception(
                'Form schema definition at index ' . $index . ' is missing required key "name"'
            );
        }
    }
}
