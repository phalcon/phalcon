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

use Closure;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\Exceptions\InvalidCallbackReturn;
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
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, string $field): bool
    {
        $callback = $this->getOption("callback");

        if (is_callable($callback)) {
            $data = $validation->getEntity();

            if (empty($data)) {
                $data = $validation->getData();
            }

            /**
             * Snapshot the message state so a setTemplate()/setTemplates()
             * call inside the bound closure cannot leak into later
             * validations that reuse this validator instance. Restored below
             * once the failure message (if any) has been built.
             */
            $savedTemplate  = $this->template;
            $savedChanged   = $this->templateChanged;
            $savedTemplates = $this->templates;

            if ($callback instanceof Closure) {
                $callback = Closure::bind($callback, $this);
            }

            $returnedValue = call_user_func($callback, $data);

            if (is_bool($returnedValue) && !$returnedValue) {
                $validation->appendMessage(
                    $this->messageFactory($validation, $field)
                );
            }

            $this->template        = $savedTemplate;
            $this->templateChanged = $savedChanged;
            $this->templates       = $savedTemplates;

            if (is_bool($returnedValue)) {
                return $returnedValue;
            }

            if ($returnedValue instanceof ValidatorInterface) {
                return $returnedValue->validate($validation, $field);
            }

            throw new InvalidCallbackReturn();
        }

        return true;
    }
}
