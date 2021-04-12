<?php
/**
 * This file is part of the Aether application.
 *
 * (c) Stephen Cox <web@stephencox.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aether\Filesystem\Service;

use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Wrapper service for Flysystem filesystem.
 */
class Filesystem
{
    /**
     * @var League\Flysystem\Filesystem
     */
    private $filesystem;

    /**
     * Initialise Filesystem.
     *
     * @param string $rootPath
     *   Root filesystem path.
     */
    public function __construct(string $rootPath)
    {
        $adapter = new LocalFilesystemAdapter($rootPath);
        $this->filesystem = new Flysystem($adapter);
    }

    /**
     * Get wrapped Filesystem.
     *
     * @return League\Flysystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}
