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

namespace Frosit;

use Composer\Autoload\ClassLoader;
use Frosit\Util\FrositIoHelper;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase.
 *
 * @todo clean and optimise
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var TestApplication
     */
    private $application;

    /**
     * @throws \Symfony\Component\Console\Exception\LogicException
     *
     * @return \Frosit\Application
     */
    public function getApplication()
    {
        $loader = new ClassLoader();
        $application = new Application($loader);
        $application->setAutoExit(false);
        $application->setVersion('1.0.0');
        $application->setName('Magento Patch Finder');
        $application->setFio(new FrositIoHelper());
//        $application->getMage('')

        return $application;
    }

    /**
     * @return bool|string
     */
    public function getMageDir()
    {
        if (defined('CURRENTDIR') && file_exists(CURRENTDIR)) {
            return CURRENTDIR;
        }

        return false;
    }
}
