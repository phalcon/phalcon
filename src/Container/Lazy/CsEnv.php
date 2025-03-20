<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Lazy;

use Phalcon\Container\Container;
use Phalcon\Container\Exception\NotDefined;

class CsEnv extends Env
{
    /**
     * @param string      $varname
     * @param string|null $vartype
     */
    public function __construct(
        protected string $varname,
        protected string | null $vartype = null
    ) {
    }

    /**
     * @param Container $container
     *
     * @return array
     * @throws NotDefined
     */
    public function __invoke(Container $container): array
    {
        $values = str_getcsv($this->getEnv(), ",", '"', "\\");

        if ($this->vartype !== null) {
            $return = [];
            foreach ($values as $key => $value) {
                settype($value, $this->vartype);
                $return[$key] = $value;
            }

            $values = $return;
        }

        return $values;
    }
}
