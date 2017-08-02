<?php
/**
 * Magepatch - Magento Patches finder & verification utility
 *
 * Copyright (c) 2017 Fabio Ros <fabio@frosit.nl> (https://frosit.nl)
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
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace Frosit\Util;

use DateTime;
use Frosit\Util\Patch\Downloader;
use Frosit\Util\Patch\Filter;
use Frosit\Util\Patch\ParseException;
use Version\Version;

/**
 * Class Patches.
 */
class Patches
{
    /*
     * Patch filters
     */
    use Filter;

    /**
     * The index collection of patches.
     *
     * @var array
     */
    protected $patches;

    /**
     * Take the patches source from the patches.json index file in case our mirror falls behind.
     *
     * @var string
     */
    protected $patchesSource;

    /**
     * Last indexation of patches.
     *
     * @var DateTime
     */
    protected $indexTime;

    /**
     * Patches constructor.
     *
     * @param $patchFile
     */
    public function __construct($patchFile)
    {
        try {
            $this->initialize($patchFile);
        } catch (ParseException $e) {
            echo "Error while parsing patches index file. \n";
        }
    }

    /**
     * @return array
     */
    public function getPatches()
    {
        return $this->patches;
    }

    /**
     * Filter patches collection by Magento installation.
     *
     * @param Mage $mage
     * @param bool $filterByApplied
     *
     * @return array|bool
     */
    public function getPatchesForMage(Mage $mage, $filterByApplied = true)
    {
        if (!$mage->hasLoaded()) {
            return false;
        }
        $patches = $this->getPatches();

        if ($mage->getEdition()) {
            $patches = $this->filterByEdition($patches, $mage->getEdition());
        }

        if ($version = Version::parse($mage->getVersion())) {
            $patches = $this->filterByVersion($patches, $version);
        }

        if ($mage->getAppliedPatches() && $filterByApplied) {
            $patches = $this->filterByApplied($patches, $mage->getAppliedPatches());
        }

        return $patches;
    }

    /**
     * @return DateTime
     */
    public function getIndexTime()
    {
        return $this->indexTime;
    }

    /**
     * @param $patches
     * @param $basedir
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function downloadPatches($patches, $basedir)
    {
        $storageDir = $basedir.DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'patches';

        if (!@mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
            throw new \RuntimeException('Could not create directory for patches at: '.$storageDir);
        }
        $downloads = [];

        foreach ($patches as $patch) {
            if (!file_exists($storageDir.DIRECTORY_SEPARATOR.$patch['patchId'].'.sh')) {
                $downloads[] = $patch;
            }
        }

        if (Downloader::downloadPatchesFromGit($downloads, $storageDir)) {
            foreach ($downloads as $patch) {
                $file = $storageDir.$patch['patchId'].'.sh';
                if (file_exists($file) && is_readable($file)) {
                    $checksum = sha1_file($file);
                    if ($checksum !== $patch['checksum']) {
                        echo 'Checksums do not match for '.$patch['patchId']."\n";
                    }
                }
            }
        }
    }

    /**
     * Initialises patches data.
     *
     * @todo add update from web
     * @todo add phar mode
     * @todo directory mess
     * @todo implement downloading from different sources according to json file
     *
     * @param $patchesFile
     *
     * @return bool
     */
    private function initialize($patchesFile)
    {
        try {
            if ($patches = file_get_contents($patchesFile)) {
                if ($patchesFile = json_decode($patches, true)) {
                    if (isset($patchesFile['patches'])) {
                        $this->patches = $patchesFile['patches'];
                    } else {
                        return false;
                    }

                    if (isset($patchesFile['patches_source'])) {
                        $this->patchesSource = $patchesFile['patches_source'];
                    }

                    if (isset($patchesFile['build'])) {
                        $this->indexTime = new DateTime();
                        $this->indexTime->setTimestamp($patchesFile['build']);
                    }

                    return true;
                }
            }
        } catch (\Exception $e) {
            echo "Could not parse patches file \n";
        }

        return false;
    }
}
