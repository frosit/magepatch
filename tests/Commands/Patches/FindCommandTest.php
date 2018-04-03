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

namespace Frosit\Commands\Patches;

use Frosit\Application;
use Frosit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FindCommandTest.
 */
class FindCommandTest extends TestCase
{
    /**
     * @var FindCommand
     */
    private $command;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->command = new FindCommand();
        $this->getApplication()->add($this->command);
    }

    /**
     * @return mixed|\Symfony\Component\Console\Command\Command
     */
    public function getCommand()
    {
        return $this->getApplication()->find('patches:find');
    }

    public function testName()
    {
        $this->assertSame('patches:find', $this->getCommand()->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertSame(
            'edition',
            $command->getDefinition()
                ->getOption('edition')->getName()
        );
    }

    public function testExecuteCommand()
    {
        if (!$this->getMageDir()) {
            $this->markTestSkipped('Could not find a Magento Directory.');
        } else {
            $command = new FindCommand();
            $command->setApplication(new Application());
            $commandTester = new CommandTester($command);
            $commandTester->setInputs(['no']);
            $commandTester->execute(
                ['command' => $command->getName(), '--mage' => $this->getMageDir()],
                ['decorated' => false]
            );
            $display = $commandTester->getDisplay();
            $this->assertRegExp('/SUPEE-/', $display);
        }
    }

    public function testAttributes()
    {
        $this->assertClassHasAttribute('version', FindCommand::class);
        $this->assertClassHasAttribute('edition', FindCommand::class);
        $this->assertClassHasAttribute('baseDir', FindCommand::class);
    }
}
