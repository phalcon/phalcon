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

namespace Phalcon\Tests\Benchmarks\Support;

use Phalcon\Support\Collection;

use function rand;
use function uniqid;

/**
 * @BeforeMethods({"setUp"})
 */
class CollectionBench
{
    private array $data = [];

    public function setUp(): void
    {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($step = 1; $step <= 100; $step++) {
            $prefix = substr($letters, rand(0, 23), 1) . $step;
            $this->data[$prefix] = uniqid('col-');
        }
    }

    /**
     * @Revs(1000)
     * @Iterations(5)
     *
     * @return void
     */
    public function benchConstruct(): void
    {
        $collection = new Collection();
    }
    /**
     * @Revs(1000)
     * @Iterations(5)
     *
     * @return void
     */
    public function benchConstructWithData(): void
    {
        $collection = new Collection($this->data);
    }
}
