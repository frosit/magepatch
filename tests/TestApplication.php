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
use PHPUnit_Framework_MockObject_Generator;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TestApplication.
 *
 * @todo clean and optimise
 */
class TestApplication
{
    /**
     * TestApplication constructor.
     *
     * @param \Composer\Autoload\ClassLoader $loader
     */
    public function __construct(ClassLoader $loader)
    {
    }

    /**
     * @var Application
     */
    private $application;

    /**
     * @throws \PHPUnit_Framework_MockObject_RuntimeException
     * @throws \PHPUnit_Framework_Exception
     * @throws \InvalidArgumentException
     *
     * @return \Frosit\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getApplication()
    {
        $mockObjectGenerator = new PHPUnit_Framework_MockObject_Generator();

        /** @var Application|PHPUnit_Framework_MockObject_MockObject $application */
        $application = $mockObjectGenerator->getMock('Frosit\Application', ['__construct']);
        $application->setAutoExit(false);
        if ($loader = file_get_contents(ROOTDIR.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php')) {
            $application->setAutoloader($loader);
        }
        $this->application = $application;

        return $application;
    }
}
