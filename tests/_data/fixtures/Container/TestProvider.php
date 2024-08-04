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

namespace Phalcon\Tests\Fixtures\Container;

use Phalcon\Container\Container;
use Phalcon\Container\Definitions\Definitions;
use Phalcon\Container\Interfaces\ProviderInterface;

class TestProvider implements ProviderInterface
{
    public function provide(Definitions $definitions) : void
    {
        $definitions->{TestWithInterface::class}->argument(0, 'ten');
        $definitions->oneval = 'oneval';
        $definitions->lazyval = $definitions->call(
            function (Container $container) {
                return 'lazyval';
            }
        );
    }
}
