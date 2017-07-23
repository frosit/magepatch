<?php
/**
 *     Magepatch - Magento Patches finder & verification utility
 *
 *     @Copyright (c) 2017 Fabio Ros (FROSIT) <info@gdprproof.com> (https://gdprproof.com)
 *     @License GNU GPLv3  (http://www.gnu.org/licenses/gpl-3.0.txt)
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 */


namespace GDPRProof\Util;

use GDPRProof\Util\Mage\Finder;

/**
 * Class Mage
 * @package GDPRProof\Util
 */
class Mage
{

    /**
     * @var string $version
     */
    protected $version;

    /**
     * @var string $edition
     */
    protected $edition;

    /**
     * Path to Mage base
     * @var string $rootDir
     */
    protected $rootDir;

    /**
     * Flag to easily determine whether it has been loaded
     * @var bool
     */
    protected $initialized = false;


    /**
     * Applied patches collection
     * @var array|false|null
     */
    protected $appliedPatches;


    /**
     * @return bool
     */
    public function hasLoaded()
    {
        return $this->initialized;
    }

    /**
     * Tries to find Mage
     * @param null $path
     * @return bool|mixed
     */
    public function find($path = null)
    {
        if ($this->rootDir === null) {
            if ($path === null) {
                $path = realpath('.');
            }
            if (substr($path, strlen($path - 5)) === 'Mage.php') {
                $this->initMage($path);
            } elseif (in_array('app', scandir($path))) {
                $this->initMage($path.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Mage.php');
            } else {
                $finder = new Finder($path);
                $finder->MageFinder($path); // @todo unstable poc faster finder
                $finder->getMagePath();
            }
        }

        return $this;
    }

    /**
     * Tries to load Mage
     * @param $path
     * @return bool
     */
    private function initMage($path)
    {
        if (file_exists($path) && is_readable($path)) {
            try {
                require_once $path;
                \Varien_Autoload::register();
                \Mage::app();
                $this->initialized = true;
                $this->getRootDir();

                return $path;
            } catch (MageException $e) {
                echo "Mage could not load \n";
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        if ($this->version === null && $this->initialized) {
            try {
                $this->setVersion(\Mage::getVersion());
            } catch (MageException $e) {
                echo $e->getMessage();
            }
        }

        return $this->version;
    }

    /**
     * @param mixed $version
     * @return Mage
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @todo not so compatible with 1.4
     * @return mixed
     */
    public function getEdition()
    {
        if ($this->edition === null && $this->initialized) {
            try {
                $this->setEdition(\Mage::getEdition());
            } catch (MageException $e) {
                echo $e->getMessage();
            }
        }

        return $this->edition;
    }

    /**
     * @param mixed $edition
     * @return Mage
     */
    public function setEdition($edition)
    {
        $this->edition = $this->sanitizeEdition($edition);

        return $this;
    }

    private function sanitizeEdition($edition)
    {
        if (strlen($edition) > 2) {
            if (strpos($edition, ',')) {
                return 'EE-CE';
            }
            $edition = strtolower(trim($edition));
            if (in_array($edition, ['community', 'comunity'], true)) { // for bad writers
                return 'CE';
            }

            if (in_array($edition, ['enterprise', 'enteprise'], true)) {
                return 'EE';
            }

            return null;
        }

        if (in_array($edition, ['ee', 'ce'], true)) {
            return strtoupper($edition);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getRootDir()
    {
        if ($this->rootDir === null && $this->initialized) {
            try {
                $this->rootDir = \Mage::getBaseDir();
            } catch (MageException $e) {
                echo $e->getMessage();
            }
        }

        return $this->rootDir;
    }

    /**
     * @param mixed $rootDir
     * @return Mage
     */
    public function setRootDir($rootDir)
    {
        if (!$this->hasLoaded()) {
            if ($path = $this->find($rootDir)) {
                $this->rootDir = $path;
            }
        }

        return $this;
    }

    /**
     * @return null|array
     */
    public function getAppliedPatches()
    {
        if ($this->appliedPatches === null) {
            $this->parseAppliedPatchFile();
        }

        return $this->appliedPatches;
    }

    public function parseAppliedPatchFile()
    {

        if (!($patchFile = realpath(
            $patchFile = $this->getRootDir().DIRECTORY_SEPARATOR.'app/etc/applied.patches.list'
        ))) {
            return false;
        }

        if (file_exists($patchFile) && is_readable($patchFile)) { // double check
            $this->appliedPatches = []; // set ampty arr
            try {
                $fh = fopen($patchFile, 'rb+');
                while (!feof($fh)) {
                    $line = fgets($fh);
                    if (strpos($line, '|') && !strpos($line, 'REVERTED')) {
                        $patch = [];
                        list($patch['date'], $patch['supee'], $patch['version'], $patch['revision'], $patch['checksum']) = array_map(
                            function ($var) {
                                return trim(str_replace('SUPEE-', '', $var));
                            },
                            explode('|', $line)
                        );
                        if (isset($patch['checksum'])) {
                            $this->appliedPatches[$patch['checksum']] = $patch;
                        } else {
                            $this->appliedPatches[] = $patch;
                        }
                    }
                }
                fclose($fh);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }


}