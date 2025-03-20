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

namespace Phalcon\Tests\Fixtures\Di;

use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Tests\Fixtures\Di\ServiceComponent;

/**
 * Class PropertiesComponent
 *
 * @package Phalcon\Tests\Fixtures\Di
 *
 * @property EscaperInterface $escaper
 * @property ServiceComponent $service
 */
class PropertiesComponent extends ServiceComponent
{
    /**
     * @var string
     */
    public string $propertyName = '';

    /**
     * @var int
     */
    public int $propertyType = 0;

    /**
     * PropertiesComponent constructor.
     *
     * @param string $name
     * @param int    $type
     */
    public function __construct(
        string $name,
        int $type,
        public EscaperInterface | null $escaper = null,
        public ServiceComponent | null $service = null
    ) {
        parent::__construct($name, $type);
    }

    public function calculate(): void
    {
        $this->type = 555;
    }

    /**
     * @return EscaperInterface|null
     */
    public function getEscaper(): ?EscaperInterface
    {
        return $this->escaper;
    }

    /**
     * @return ?ServiceComponent
     */
    public function getService(): ?ServiceComponent
    {
        return $this->service;
    }

    /**
     * @param int $transformType
     */
    public function transform(int $transformType)
    {
        $this->type = $transformType;
    }
}
