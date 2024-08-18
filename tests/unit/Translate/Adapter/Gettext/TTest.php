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

namespace Phalcon\Tests\Unit\Translate\Adapter\Gettext;

use Phalcon\Tests\Fixtures\Traits\TranslateGettextHelperTrait;
use Phalcon\Tests\Fixtures\Traits\TranslateGettextTrait;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension('gettext')]
final class TTest extends AbstractUnitTestCase
{
    use TranslateGettextTrait;
    use TranslateGettextHelperTrait;

    /**
     * @return string
     */
    protected function func(): string
    {
        return 't';
    }
}
