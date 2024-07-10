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

namespace Phalcon\Mvc\Model;

use Phalcon\Mvc\ModelInterface;

/**
 * Interface for Phalcon\Mvc\Model\Behavior
 */
interface BehaviorInterface
{
    /**
     * Calls a method when it's missing in the model
     *
     * @param ModelInterface $model
     * @param string         $method
     * @param array          $arguments
     *
     * @return mixed
     */
    public function missingMethod(
        ModelInterface $model,
        string $method,
        array $arguments = []
    );

    /**
     * This method receives the notifications from the EventsManager
     *
     * @param string         $type
     * @param ModelInterface $model
     *
     * @return mixed
     */
    public function notify(string $type, ModelInterface $model);
}
