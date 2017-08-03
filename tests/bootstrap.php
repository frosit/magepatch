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

use Frosit\TestApplication;

/*
 * @todo clean out redundant code
 * @todo rename CURRENTDIR, it targets the mage dir in test setup
 *
 *
 */

/*
 * @todo probably redundant
 */
error_reporting(-1);
ini_set('display_errors', '1');
$mageDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.magedir';
if (file_exists($mageDir) && file_get_contents($mageDir)) {
    $base = file_get_contents($mageDir);
    if ($base[0] === DIRECTORY_SEPARATOR && is_dir($base)) {
        define(CURRENTDIR, $base);
    } else {
        define('CURRENTDIR', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.trim($base, "/.\n")));
    }
} else {
    define('CURRENTDIR', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR));
}
define('APPDIR', dirname(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.'));
define('PHARDIR', Phar::running());
if (PHARDIR) {
    define('ROOTDIR', realpath(PHARDIR));
} else {
    define('ROOTDIR', realpath(APPDIR));
}
@session_start();
$loader = require __DIR__.'/../vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */
$loader->setUseIncludePath(true);

if (isset($base)) {
    $paths = [
        CURRENTDIR.'/app/code/local',
        CURRENTDIR.'/app/code/community',
        CURRENTDIR.'/app/code/core',
        CURRENTDIR.'/lib',
    ];
    set_include_path(implode(PATH_SEPARATOR, $paths).PATH_SEPARATOR.get_include_path());
}
unset($paths, $base);
$application = new TestApplication($loader);
