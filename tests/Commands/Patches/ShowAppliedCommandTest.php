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
 * Class ShowAppliedCommandTest.
 *
 * @todo add scenario with and without a applied patches file present
 * @todo clean class
 */
class ShowAppliedCommandTest extends TestCase
{
    /**
     * @var ShowAppliedCommand
     */
    private $command;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->command = new ShowAppliedCommand();
        $this->getApplication()->add(new ShowAppliedCommand());
    }

    /**
     * @return mixed|\Symfony\Component\Console\Command\Command
     */
    public function getCommand()
    {
        return $this->getApplication()->find('patches:show-applied');
    }

    public function testName()
    {
        $this->assertSame('patches:show-applied', $this->getCommand()->getName());
    }

    public function testArguments()
    {
        $command = $this->getCommand();
        $this->assertSame(
            'patchfile',
            $command->getDefinition()->getArgument('patchfile')->getName()
        );
    }

    public function testExecuteCommand()
    {
        //        $this->markTestSkipped('finish test');
        //        $command = $this->getCommand();
        if (!$this->getMageDir()) {
            $this->markTestSkipped('No mage dir found...');
        } else {
            $command = new ShowAppliedCommand();
            $command->setApplication(new Application());
            $commandTester = new CommandTester($command);
            $commandTester->setInputs(['no']);
            $commandTester->execute(
                ['command' => $command->getName(), '--mage' => $this->getMageDir()],
                ['decorated' => false]
            );
            $this->assertContains(
                'Could not parse applied patches',
                $commandTester->getDisplay(),
                'no applied patches to parse'
            );
        }
    }

    public function testAttributes()
    {
        $this->assertClassHasAttribute('baseDir', ShowAppliedCommand::class);
    }
}
