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

namespace GDPRProof\Util\Patch;

use Version\Version;

/**
 * Trait Filter.
 */
trait Filter
{
    /**
     * @param $patches
     * @param Version $version
     *
     * @return array
     */
    public function filterByVersion($patches, Version $version)
    {
        $matchingPatches = [];
        foreach ($patches as $patch) {
            if ($patch['versionFrom'] === $patch['versionTo']) {
                $patchVersion = Version::parse($patch['versionFrom']);
                $compare = $version->compare($patchVersion);
                if ($compare === 0) {
                    $matchingPatches[] = $patch;
                }
            } else {
                $fromVersion = Version::parse($patch['versionFrom']);
                $toVersion = Version::parse($patch['versionTo']);
                if ($version->compare($fromVersion) !== -1 && $version->compare($toVersion) !== 1) {
                    $matchingPatches[] = $patch;
                }
            }
        }

        return $matchingPatches;
    }

    /**
     * @param $patches
     * @param $edition
     *
     * @return array
     */
    public function filterByEdition($patches, $edition)
    {
        $matches = [];
        foreach ($patches as $patch) {
            if ($patch['edition'] === $edition || $patch['edition'] === 'EE-CE') {
                $matches[$patch['uid']] = $patch;
            }
        }

        return $matches;
    }

    /**
     * @param $patches
     * @param $appliedPatches
     *
     * @return array
     */
    public function filterByApplied($patches, $appliedPatches)
    {
        $appliedPatchesChecksums = array_column($appliedPatches, 'checksum_patch');

        $matches = [];
        foreach ($patches as $patch) {
            if (isset($patch['patchChecksum'])) {
                if (in_array($patch['patchChecksum'], $appliedPatchesChecksums, true)) {
                    $patch['checksum_match'] = true;
                }
            }
            if (isset($patch['checksum_match'])) {
                if (!$patch['checksum_match']) {
                    foreach ($appliedPatchesChecksums as $appliedPatch) {
                        if ($appliedPatch['supee'] === $patch['supee'] && $appliedPatch['revision'] === $patch['revision']) {
                            $patch['supee_rev_match'] = true;
                        } else {
                            $patch['supee_rev_match'] = false;
                        }
                    }
                }

                if ($patch['checksum_match']) {
                    $patch['applied'] = 'yes';
                } elseif ($patch['supee_rev_match']) {
                    $patch['applied'] = 'yes, but different checksum';
                } else {
                    $patch['applied'] = 'no';
                }
            }

            $matches[] = $patch;
        }

        return $matches;
    }
}
