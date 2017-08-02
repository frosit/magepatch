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

/**
 * Class Diff.
 */
class Diff
{
    /**
     * @param $file
     * @param $mageDir
     *
     * @return bool|mixed
     */
    public static function extractDiff($file, $mageDir)
    {
        if ($file[0] !== DIRECTORY_SEPARATOR) {
            $file = $mageDir.DIRECTORY_SEPARATOR.$file;
        }

        if (file_exists($file) && is_readable($file)) {
            if ($content = file_get_contents($file)) {
                $content = explode("__PATCHFILE_FOLLOWS__\n", $content);
                $content = end($content);
                $diffFile = str_replace('.sh', '.diff', $file);

                if (file_put_contents($diffFile, $content)) {
                    unset($content);

                    return $diffFile;
                }
            }
        }

        return false;
    }
}
