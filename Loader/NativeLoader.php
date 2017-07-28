<?php

namespace Piszczek\PimcoreFixturesBundle\Loader;

use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\Loader\NativeLoader as BaseNativeLoader;

class NativeLoader extends BaseNativeLoader
{
    public function createFileLoader(): FileLoaderInterface
    {
        return new PimcoreFileLoader(
            $this->getParser(),
            $this->getDataLoader()
        );
    }
}