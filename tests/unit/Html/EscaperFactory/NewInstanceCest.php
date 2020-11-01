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

namespace Phalcon\Tests\Unit\Html\EscaperFactory;

use Phalcon\Html\Escaper;
use Phalcon\Html\EscaperFactory;
use Phalcon\Html\EscaperInterface;
use UnitTester;

class NewInstanceCest
{
    /**
     * Tests Phalcon\Logger\EscaperFactory :: newInstance()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function loggerEscaperFactoryNewInstance(UnitTester $I)
    {
        $I->wantToTest('Logger\EscaperFactory - newInstance()');

        $factory = new EscaperFactory();
        $escaper = $factory->newInstance();

        $I->assertInstanceOf(EscaperInterface::class, $escaper);
        $I->assertInstanceOf(Escaper::class, $escaper);
    }
}
