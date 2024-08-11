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

namespace Phalcon\Tests\Fixtures\Listener;

use Phalcon\Events\Event;
use Phalcon\Tests\AbstractUnitTestCase;

/**
 * Class ViewCompileListener
 */
class ViewCompileListener
{
    /** @var AbstractUnitTestCase */
    protected $testCase;

    protected $before = '';
    protected $after  = '';

    public function setTestCase(AbstractUnitTestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @param $event
     * @param $component
     */
    public function beforeCompile($event, $component)
    {
        $this->testCase->assertInstanceOf(
            Event::class,
            $event
        );

        $this->before = 'Before fired';
    }

    /**
     * @param $event
     * @param $component
     */
    public function afterCompile($event, $component)
    {
        $this->testCase->assertInstanceOf(
            Event::class,
            $event
        );

        $this->after = 'After fired';
    }

    public function getAfter(): string
    {
        return $this->after;
    }

    public function getBefore(): string
    {
        return $this->before;
    }
}
