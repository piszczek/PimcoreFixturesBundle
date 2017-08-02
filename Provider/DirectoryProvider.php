<?php

declare(strict_types=1);

namespace Piszczek\PimcoreFixturesBundle\Provider;

class DirectoryProvider
{
    /**
     * @param $sourcePath
     * @return string
     */
    public function path($sourcePath): string
    {
        $rootDir = \Pimcore::getKernel()->getRootDir();

        return $rootDir . DIRECTORY_SEPARATOR . $sourcePath;
    }
}
