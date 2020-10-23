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
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\InterpolatorFactory;
use UnitTester;

class QueryCest
{
    use TranslateGettextTrait;
    use TranslateGettextHelperTrait;

    /**
     * @return string
     */
    protected function func(): string
    {
        return 'query';
    }

//
//    /**
//     * Tests Phalcon\Translate\Adapter\Gettext :: query()
//     *
//     * @param UnitTester $I
//     *
//     * @author Phalcon Team <team@phalcon.io>
//     * @since  2020-09-09
//     */
//    public function translateAdapterGettextQuery(UnitTester $I)
//    {
//        $I->wantToTest('Translate\Adapter\Gettext - query()');
//
//        $params     = $this->getGettextConfig();
//        $translator = new Gettext(new InterpolatorFactory(), $params);
//
//        $expected = 'Hello';
//        $actual   = $translator->query('hi');
//        $I->assertEquals($expected, $actual);
//
//        $expected = 'Hello Jeremy';
//        $actual   = $translator->query('hello-key', ['name' => 'Jeremy']);
//        $I->assertEquals($expected, $actual);
//
//        $aParamQuery = ['song' => 'Phalcon rocks', 'artist' => 'Phalcon team'];
//        $expected    = 'The song is Phalcon rocks (Phalcon team)';
//        $actual      = $translator->query('song-key', $aParamQuery);
//        $I->assertEquals($expected, $actual);
//    }
}
