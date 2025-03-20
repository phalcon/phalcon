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

namespace Phalcon\Filter\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\ValidatorInterface;

use function call_user_func;
use function is_bool;
use function is_callable;

/**
 * Calls user function for validation
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Callback as CallbackValidator;
 * use Phalcon\Filter\Validation\Validator\Numericality as NumericalityValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     ["user", "admin"],
 *     new CallbackValidator(
 *         [
 *             "message" => "There must be only an user or admin set",
 *             "callback" => function($data) {
 *                 if (!empty($data->getUser()) && !empty($data->getAdmin())) {
 *                     return false;
 *                 }
 *
 *                 return true;
 *             }
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     "amount",
 *     new CallbackValidator(
 *         [
 *             "callback" => function($data) {
 *                 if (!empty($data->getProduct())) {
 *                     return new NumericalityValidator(
 *                         [
 *                             "message" => "Amount must be a number."
 *                         ]
 *                     );
 *                 }
 *             }
 *         ]
 *     )
 * );
 * ```
 */
class Callback extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must match the callback function";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $callback = $this->getOption("callback");

        if (is_callable($callback)) {
            $data = $validation->getEntity();

            if (empty($data)) {
                $data = $validation->getData();
            }

            $returnedValue = call_user_func($callback, $data);

            if (is_bool($returnedValue)) {
                if (true !== $returnedValue) {
                    $validation->appendMessage(
                        $this->messageFactory($validation, $field)
                    );

                    return false;
                }

                return true;
            } elseif ($returnedValue instanceof ValidatorInterface) {
                return $returnedValue->validate($validation, $field);
            }

            throw new Exception(
                "Callback must return bool or "
                . "Phalcon\\Filter\\Validation\\Validator object"
            );
        }

        return true;
    }
}
