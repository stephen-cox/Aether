<?php

namespace Aether\Filesystem\Service;

use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Filesystem
{
    private $filesystem;

    public function __construct(string $rootPath)
    {
        $adapter = new LocalFilesystemAdapter($rootPath);
        $this->filesystem = new Flysystem($adapter);
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

}
