<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Annotations\Fake;

use Phalcon\Annotations\AnnotationsFactory;

/**
 * Overrides load() with the cphalcon signature
 */
class FakeAnnotationsFactoryOverride extends AnnotationsFactory
{
    public function load($config)
    {
        return parent::load($config);
    }
}
