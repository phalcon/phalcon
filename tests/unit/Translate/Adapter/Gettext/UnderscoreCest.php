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

class UnderscoreCest
{
    use TranslateGettextTrait;
    use TranslateGettextHelperTrait;

    /**
     * @return string
     */
    protected function func(): string
    {
        return '_';
    }


//    /**
//     * Tests Phalcon\Translate\Adapter\Gettext :: _()
//     *
//     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
//     * @since  2020-01-06
//     */
//    public function translateAdapterGettextUnderscore(UnitTester $I)
//    {
//        $I->wantToTest("Translate\Adapter\Gettext - _()");
//
//        $params     = $this->getGettextConfig();
//        $translator = new Gettext(
//            new InterpolatorFactory(),
//            $params
//        );
//
//        $I->assertEquals('Hello', $translator->_('hi'));
//
//        $I->assertEquals('Hello Jeremy', $translator->_('hello-key', ['name' => 'Jeremy']));
//
//        $sResultTranslate = $translator->_('song-key', ['song' => 'Phalcon rocks', 'artist' => 'Phalcon team']);
//
//        $I->assertEquals('The song is Phalcon rocks (Phalcon team)', $sResultTranslate);
//    }
}
