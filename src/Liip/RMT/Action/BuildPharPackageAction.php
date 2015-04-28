<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Action;

use Phar;
use FilesystemIterator;
use Liip\RMT\Context;

/**
 * Builds a Phar package of the current project.
 */
class BuildPharPackageAction extends BaseAction
{

    public function __construct($options)
    {
        $this->options = array_merge(array(
            'package-name' => 'rmt-package',
            'destination' => '/tmp/'
        ), $options);
    }

    public function execute()
    {
        $this->create();
        $this->confirmSuccess();
    }

    /**
     * Handles the creation of the package.
     */
    protected function create()
    {
        $output = $this->getDestination() . '/' . $this->getFilename();

        $phar = new Phar($output, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME);
        $phar->buildFromDirectory(Context::getParam('project-root'));
        $phar->setStub($phar->createDefaultStub("index.php")); // TODO: improve stub
    }

    /**
     * Determines the package filename based on the next version and the 'package-name' option.
     *
     * @return string
     */
    protected function getFilename()
    {
        $currentVersion = Context::get('version-persister')->getCurrentVersion();
        $nextVersion = Context::get('version-generator')->generateNextVersion($currentVersion);

        return $this->options['package-name'] . '-' . $nextVersion . '.phar';
    }

    /**
     * Checks if the path is relative.
     *
     * @param $path string The path to check
     * @return bool
     */
    protected function isRelativePath($path)
    {
        return strpos($path, '/') !== 0;
    }

    /**
     * Get the destination directory to build the package into.
     *
     * @return string The destination
     */
    protected function getDestination()
    {
        $destination = $this->options['destination'];

        if ($this->isRelativePath($destination)) {
            return Context::getParam('project-root') . '/' . $destination;
        }

        return $destination;
    }
}