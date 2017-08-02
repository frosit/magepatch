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

namespace Frosit\Util\Patch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Downloader.
 */
class Downloader
{
    /**
     * GIT Downloader URL.
     */
    const GIT = 'https://github.com/brentwpeterson/magento-patches';

    /**
     * @param $patches
     * @param $to
     *
     * @throws \LogicException
     *
     * @return mixed
     */
    public static function downloadPatchesFromGit($patches, $to)
    {
        $client = new Client(['base_uri' => self::GIT.DIRECTORY_SEPARATOR.'raw/master/']);
        $downloads = [];
        foreach ($patches as $patch) {
            $downloadLocation = $to.DIRECTORY_SEPARATOR.$patch['uid'].'.sh';
            $download = $client->getAsync(
                $patch['location'],
                ['sink' => $downloadLocation]
            );
            $download->then(
                function (ResponseInterface $res) {
                    echo '- '.$res->getStatusCode()."\n";
                },
                function (RequestException $e) {
                    echo '* Failed: '.$e->getMessage()."\n";
                }
            );
            $downloads[] = $download;
        }

        if ($results = Promise\settle($downloads)->wait()) {
            return $results;
        }

        return false;
    }
}
