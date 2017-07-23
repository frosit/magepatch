<?php
/**
 * Magepatch - Magento Patches finder & verification utility
 *
 * @Copyright (c) 2017 Fabio Ros (FROSIT) <info@frosit.nl> (https://frosit.nl)
 * @License GNU GPLv3  (http://www.gnu.org/licenses/gpl-3.0.txt)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

namespace GDPRProof\Util\Mage;

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class Mage.
 *
 * A conceptual Mage finder class.
 *
 * @deprecated
 */
class Finder
{
    const DOCROOT = 1;
    const MAGEROOT = 2;
    const MAXTRAVERSALS = 4;

    /**
     * Current users' location in system.
     *
     * @var string
     */
    private $userLocation;

    /**
     * Flag whether Magento is initialized yet.
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * @var
     */
    private $magePath;

    /**
     * @todo verify regex
     * Regex matching directories to ignore
     */
    const IGNOREDIRECTORIESREGEX = '/^(\.git|cache|var|logs)+/';

    /**
     * Mage constructor.
     *
     * @param $userDir
     */
    public function __construct($userDir)
    {
        $this->userLocation = $userDir;
    }

    /**
     * Traverses directories in a special order to find Mage.php in the shortest amount of time.
     *
     * @param $baseDirectory
     *
     * @return bool
     */
    public function MageFinder($baseDirectory)
    {
        if (!$this->iterateDir($baseDirectory)) {
            $dir_iterator = new RecursiveDirectoryIterator($baseDirectory, FilesystemIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

            while ($iterator->valid()) {
                $depth = $iterator->getDepth();

                /** @var SplFileInfo $current */
                $current = $iterator->current();

                if (!$current->isDir() || preg_match(self::IGNOREDIRECTORIESREGEX, $current->getBasename())) {
                    $iterator->rewind();
                } elseif ($current->isDir() && in_array(
                        $current->getFilename(),
                        ['app', 'lib', 'public', 'public_html', 'htdocs', 'www'],
                        true
                    )
                ) {
                    if ($this->iterateDir($current->getPathname())) {
                        if ($this->initialized) {
                            return true;
                        }
                    }
                }

                $iterator->next();
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * @param $baseDirectory
     * @param int $depth
     *
     * @return bool|Finder
     */
    private function iterateDir($baseDirectory, $depth = 0)
    {
        // Directories to traverse first
        // Additional strategies are open to option
        $traverseDirs = [
            'app' => ['type' => 2],
            'lib' => ['type' => 2],
            'public' => ['type' => 1],
            'public_html' => ['type' => 1],
            'htdocs' => ['type' => 1],
            'www' => ['type' => 1],
            'webroot' => ['type' => 1],
            'staging' => ['type' => 0],
        ];

        $traversableDirs = array_keys($traverseDirs);
        $dirIterator = new DirectoryIterator($baseDirectory);
        while ($dirIterator->valid()) {
            if ($dirIterator->isDir() && in_array($dirIterator->getFilename(), $traversableDirs, true)) {
                $dirType = $traverseDirs[$dirIterator->getFilename()]['type'];

                // App/lib detected dir
                if ($dirType === 2) {
                    $parts = explode(DIRECTORY_SEPARATOR, $dirIterator->getPathname());
                    array_pop($parts);
                    $mage = implode(DIRECTORY_SEPARATOR, array_merge($parts, ['app', 'Mage.php']));
                    if (file_exists($mage)) {
                        return $this->setMagePath($mage);
                    }
                } elseif ($dirType === 1) {
                    $mage = $dirIterator->getPathname().implode(DIRECTORY_SEPARATOR, ['app', 'Mage.php']);
                    if (file_exists($mage)) {
                        return $this->setMagePath($mage);
                    }
                    // @todo more
                } else {
                    if ($depth < self::MAXTRAVERSALS) {
                        if ($mage = $this->iterateDir($dirIterator->getPathname(), $depth++)) {
                            return $this->setMagePath($mage);
                        }
                    }
                }
            }
            $dirIterator->next();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getMagePath()
    {
        return $this->magePath;
    }

    /**
     * @param $path
     *
     * @return $this
     */
    public function setMagePath($path)
    {
        if (file_exists($path) && is_readable($path)) {
            $this->magePath = $path;
            try {
                require_once $this->magePath;
                \Varien_Autoload::register();
                $this->initialized = true;
            } catch (MageException $e) {
                echo "Mage could not load \n";
            }
        }

        return $this;
    }
}
