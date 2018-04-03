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
use Frosit\Commands\InfoCommand;
use Frosit\Commands\Patches\ExtractDiffCommand;
use Frosit\Commands\Patches\FindCommand;
use Frosit\Commands\Patches\ShowAppliedCommand;
use Frosit\Util\FrositIoHelper;
use Frosit\Util\Mage;
use Phar;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application.
 */
class Application extends BaseApplication
{
    /**
     * Frosit.
     */
    const APP_NAME = '<comment>Magento Patchfinder</comment>';

    /**
     * App Version.
     */
    const APP_VERSION = '0.1.0';

    /**
     * @var string
     */
    private static $logo = "
 ______                    ______                _     
|  ___ \                  (_____ \     _        | |    
| | _ | | ____  ____  ____ _____) )___| |_  ____| | _  
| || || |/ _  |/ _  |/ _  )  ____/ _  |  _)/ ___) || \ 
| || || ( ( | ( ( | ( (/ /| |   ( ( | | |_( (___| | | |
|_||_||_|\_||_|\_|| |\____)_|    \_||_|\___)____)_| |_|
              (_____|                                  
    ";

    /**
     * @var ClassLoader|null
     */
    private $autoloader;

    /**
     * @var FrositIoHelper
     */
    public $fio;

    /**
     * @var
     */
    public $isPhar;

    /**
     * Custom Magento wrapper class.
     *
     * @var Mage
     */
    public $mage;

    /**
     * @var array
     */
    public $patches;

    /**
     * Application constructor.
     *
     * @param null $autoloader
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($autoloader = null)
    {
        $this->autoloader = $autoloader;

        $this->setHelperSet(new HelperSet([]));

        $this->add(new InfoCommand());
        $this->add(new ShowAppliedCommand());
        $this->add(new ExtractDiffCommand());
        $this->add(new FindCommand());

        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    /**
     * @param null $magePath
     *
     * @return Mage
     */
    public function getMage($magePath = null)
    {
        if ($this->mage === null) {
            if ($magePath === null) {
                $magePath = realpath('.'); // where the user is now
            }
            $this->mage = new Mage($magePath);
        }

        return $this->mage;
    }

    /**
     * Gets the default input definition.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(
            [
                new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

                new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
                new InputOption(
                    '--verbose',
                    '-v|vv|vvv',
                    InputOption::VALUE_NONE,
                    'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'
                ),
            ]
        );
    }

    /**
     * Checks wether it is in phar mode and optionally, returns the path.
     *
     * @todo finish path things
     *
     * @return bool
     *
     * @internal param bool $returnPath
     */
    public function isPhar()
    {
        if ($this->isPhar === null || $this->isPhar) {
            $this->isPhar = Phar::running() ?: false;
        }

        return $this->isPhar;
    }

    /**
     * Returns the root directory.
     *
     * @param int $levelsMin (amount of levels lower than root)
     *
     * @return string
     */
    public function getRootDir($levelsMin = 0)
    {
        ++$levelsMin; // Always go one directory lower since root is there
        if ($path = Phar::running()) {
            return $path;
        }

        $path = realpath(__DIR__);
        $dirs = explode(DIRECTORY_SEPARATOR, $path);
        $dirs = array_splice($dirs, 0, (count($dirs) - $levelsMin));

        // @todo maybe merge return implode with splice
        return implode(DIRECTORY_SEPARATOR, $dirs);
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return self::$logo.parent::getHelp();
    }

    /**
     * @param null $output
     *
     * @return FrositIoHelper
     */
    public function getFio($output = null)
    {
        if ($this->fio === null) {
            $this->setFio(new FrositIoHelper(null, $output));
        }

        return $this->fio;
    }

    /**
     * @param mixed $fio
     */
    public function setFio($fio)
    {
        $this->fio = $fio;
    }

    /**
     * @return string
     */
    public function getLongVersion()
    {
        return parent::getLongVersion().' by Fabio Ros - Magento Developer';
    }

    /**
     * @return ClassLoader
     */
    public function getAutoloader()
    {
        if ($this->autoloader === null) {
            $this->autoloader = new ClassLoader();
        }

        return $this->autoloader;
    }

    /**
     * @param ClassLoader $autoloader
     */
    public function setAutoloader(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * @param InputInterface  $input  [optional]
     * @param OutputInterface $output [optional]
     *
     * @throws \Exception
     *
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $fio = $this->getFio($output);
        $output = $fio->addOutputStyles($output);

        $this->configureIO($input, $output);

        $return = parent::run($input, $output);

        if ($return === null) {
            return 0;
        }

        return $return;
    }

    /**
     * @param ClassLoader $loader
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     *
     * @return Application
     */
    public static function createApplication(ClassLoader $loader)
    {
        return new self($loader);
    }
}
