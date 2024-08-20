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

namespace Phalcon\Tests\Unit\Support\Registry;

use Phalcon\Support\Registry;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

abstract class AbstractRegistryTestCase extends AbstractUnitTestCase
{
    protected function getData(): array
    {
        return [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];
    }
}
