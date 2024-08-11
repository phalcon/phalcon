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

namespace Phalcon\Tests\Unit\Translate\Adapter\Csv;

use Phalcon\Tests\Fixtures\Traits\TranslateCsvHelperTrait;
use Phalcon\Tests\Fixtures\Traits\TranslateCsvTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class QueryTest extends AbstractUnitTestCase
{
    use TranslateCsvTrait;
    use TranslateCsvHelperTrait;

    /**
     * @return string
     */
    protected function func(): string
    {
        return 'query';
    }
}
