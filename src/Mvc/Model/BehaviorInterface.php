<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\Model;

use Phiz\Mvc\ModelInterface;

/**
 * Phiz\Mvc\Model\BehaviorInterface
 *
 * Interface for Phiz\Mvc\Model\Behavior
 */
interface BehaviorInterface
{
    /**
     * Calls a method when it's missing in the model
     */
    public function missingMethod(ModelInterface $model, string $method, array $arguments = []) : bool;

    /**
     * This method receives the notifications from the EventsManager
     */
    public function notify(string $type,  ModelInterface $model);
}