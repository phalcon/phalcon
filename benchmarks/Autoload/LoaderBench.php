<?php

declare(strict_types=1);

namespace Benchmarks\Autoload;

use Phalcon\Autoload\Loader;

final class LoaderBench
{
    /**
     * @Revs(20000)
     * @Iterations(10)
     */
    public function benchAddClass(): void
    {
        $loader = new Loader();
        $loader->addClass('className', 'fileName');
    }

    /**
     * @Revs(20000)
     * @Iterations(10)
     */
    public function benchAddExtension(): void
    {
        $loader = new Loader();
        $loader->addExtension('extension');
    }

    /**
     * @Revs(20000)
     * @Iterations(10)
     */
    public function benchAddFile(): void
    {
        $loader = new Loader();
        $loader->addFile('fileName');
    }

    /**
     * @Revs(20000)
     * @Iterations(10)
     */
    public function benchAddNamespace(): void
    {
        $loader = new Loader();
        $loader->addNamespace('Some\Random\Namespace', 'dir1');
    }
}
