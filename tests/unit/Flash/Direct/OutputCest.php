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

namespace Phalcon\Tests\Unit\Flash\Direct;

use Codeception\Example;
use Phalcon\Flash\Direct;
use Phalcon\Html\Escaper;
use UnitTester;

use function ob_clean;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

use const PHP_EOL;

/**
 * Class OutputCest
 *
 * @package Phalcon\Tests\Unit\Flash\Direct
 */
class OutputCest
{
    /**
     * Tests Phalcon\Flash\Direct :: output() - combinations
     *
     * @dataProvider getExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function flashDirectOutputCombinations(UnitTester $I, Example $example)
    {
        $I->wantToTest('Flash\Direct - output() - ' . $example['label']);

        $flash = new Direct(new Escaper());
        $flash->setCssClasses($example['classes']);

        $message = $example['message'];
        $flash
            ->setAutomaticHtml($example['autoHtml'])
            ->setAutoescape($example['autoescape'])
            ->setCustomTemplate($example['template'])
            ->setImplicitFlush($example['implicit']);

        if (true === $example['implicit']) {
            ob_start();
            $flash->success($message);
            $actual = ob_get_contents();
            ob_end_clean();
        } else {
            $actual = $flash->success($message);
        }

        $expected = $example['expected'];
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Flash\Direct :: output()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function flashDirectOutput(UnitTester $I)
    {
        $I->wantToTest('Flash\Direct - output()');

        $flash = new Direct(new Escaper());
        $flash->setImplicitFlush(false);
        $flash->success('hello');
        $flash->warning('goodbye');

        ob_start();
        $flash->output(false);
        $actual = ob_get_contents();
        ob_clean();
        $expected = '<div class="successMessage">hello</div>' . PHP_EOL .
            '<div class="warningMessage">goodbye</div>' . PHP_EOL;
        $I->assertEquals($expected, $actual);

        /**
         * remove them
         */
        ob_start();
        $flash->output();
        $actual = ob_get_contents();
        ob_clean();
        $expected = '<div class="successMessage">hello</div>' . PHP_EOL .
            '<div class="warningMessage">goodbye</div>' . PHP_EOL;
        $I->assertEquals($expected, $actual);

        ob_start();
        $flash->output();
        $actual = ob_get_contents();
        ob_clean();
        $expected = '';
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function getExamples(): array
    {
        $message  = 'sample <phalcon> message';
        $template = '<span class="{cssClass}">{message}</span>';
        $classes  = [
            'error'   => 'alert alert-error',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-notice',
            'warning' => 'alert alert-warning',
        ];

        // 'classes,html,escape,implicit,template',
        return [
            [
                'label'      => 'xxxxx',
                'message'    => $message,
                'expected'   => '<span class="alert alert-success">sample &lt;phalcon&gt; message</span>',
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'xxxxo',
                'message'    => $message,
                'expected'   => '<div class="alert alert-success">sample &lt;phalcon&gt; message</div>' . PHP_EOL,
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'xxxox',
                'message'    => $message,
                'expected'   => '<span class="alert alert-success">sample &lt;phalcon&gt; message</span>',
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'xxxoo',
                'message'    => $message,
                'expected'   => '<div class="alert alert-success">sample &lt;phalcon&gt; message</div>' . PHP_EOL,
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'xxoxx',
                'message'    => $message,
                'expected'   => '<span class="alert alert-success">sample <phalcon> message</span>',
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'xxoxo',
                'message'    => $message,
                'expected'   => '<div class="alert alert-success">sample <phalcon> message</div>' . PHP_EOL,
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'xxoox',
                'message'    => $message,
                'expected'   => '<span class="alert alert-success">sample <phalcon> message</span>',
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'xxooo',
                'message'    => $message,
                'expected'   => '<div class="alert alert-success">sample <phalcon> message</div>' . PHP_EOL,
                'classes'    => $classes,
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'xoxxx',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => '<span class="{cssClass}">{message}</span>',
            ],
            [
                'label'      => 'xoxxo',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'xoxox',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'xoxoo',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'xooxx',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'xooxo',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'xooox',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'xoooo',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => $classes,
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'oxxxx',
                'message'    => $message,
                'expected'   => '<span class="">sample &lt;phalcon&gt; message</span>',
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'oxxxo',
                'message'    => $message,
                'expected'   => '<div>sample &lt;phalcon&gt; message</div>' . PHP_EOL,
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'oxoxx',
                'message'    => $message,
                'expected'   => '<span class="">sample <phalcon> message</span>',
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'oxoxo',
                'message'    => $message,
                'expected'   => '<div>sample <phalcon> message</div>' . PHP_EOL,
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'oxxox',
                'message'    => $message,
                'expected'   => '<span class="">sample &lt;phalcon&gt; message</span>',
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'oxxoo',
                'message'    => $message,
                'expected'   => '<div>sample &lt;phalcon&gt; message</div>' . PHP_EOL,
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'oxoox',
                'message'    => $message,
                'expected'   => '<span class="">sample <phalcon> message</span>',
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'oxooo',
                'message'    => $message,
                'expected'   => '<div>sample <phalcon> message</div>' . PHP_EOL,
                'classes'    => [],
                'autoHtml'   => true,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'ooxxx',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'ooxxo',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'ooxox',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'ooxoo',
                'message'    => $message,
                'expected'   => 'sample &lt;phalcon&gt; message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => true,
                'implicit'   => false,
                'template'   => '',
            ],
            [
                'label'      => 'oooxx',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => $template,
            ],
            [
                'label'      => 'oooxo',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => true,
                'template'   => '',
            ],
            [
                'label'      => 'oooox',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => $template,
            ],
            [
                'label'      => 'ooooo',
                'message'    => $message,
                'expected'   => 'sample <phalcon> message',
                'classes'    => [],
                'autoHtml'   => false,
                'autoescape' => false,
                'implicit'   => false,
                'template'   => '',
            ],
        ];
    }
}
