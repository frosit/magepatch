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

namespace Frosit\Commands;

use Frosit\Application;
use Frosit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class InfoCommandTest.
 */
class InfoCommandTest extends TestCase
{
    /**
     * @var \Frosit\Commands\InfoCommand
     */
    private $command;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->command = new InfoCommand();
    }

    /**
     * @return mixed|\Symfony\Component\Console\Command\Command
     */
    public function getCommand()
    {
        return $this->getApplication()->find('info');
    }

    public function testName()
    {
        $this->assertSame('info', $this->getCommand()->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertSame(
            'mage',
            $command->getDefinition()
                ->getOption('mage')->getName()
        );
    }

    public function testExecuteListsCommands()
    {
        if (!$this->getMageDir()) {
            $this->markTestSkipped('Could not find a Magento Directory.');
        } else {
            $command = new InfoCommand();
            $command->setApplication(new Application());
            $commandTester = new CommandTester($command);

            $commandTester->execute(
                ['command' => $command->getName(), '--mage' => $this->getMageDir()],
                ['decorated' => false]
            );

            $this->assertRegExp(
                '/Magento Location/',
                $commandTester->getDisplay(),
                'The command returned no Magento location.'
            );
            $this->assertRegExp('/App Version:/', $commandTester->getDisplay(), 'The command returned no app version');
        }
    }

    public function testExecuteNoMageParameter()
    {
        $command = new InfoCommand();
        $command->setApplication(new Application());
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            ['command' => $command->getName(), '--nomage' => true],
            ['decorated' => false]
        );

        $output = $commandTester->getDisplay();

        $this->assertNotRegExp('/Magento Location/', $output, 'The command returned no Magento location.');
        $this->assertContains('App Version', $output, 'The command returned no app version');
    }

    public function testExecuteFindMageDirectory()
    {
        if (defined('ROOTDIR') && file_exists(ROOTDIR.DIRECTORY_SEPARATOR.'.magedir')) {
            $mageDir = trim(file_get_contents(ROOTDIR.DIRECTORY_SEPARATOR.'.magedir'));
            if (!file_exists($mageDir) || !chdir($mageDir)) {
                $this->markTestSkipped('Could not step into a Magento Directory for this test.');
            }


            $command = new InfoCommand();
            $command->setApplication(new Application());
            $commandTester = new CommandTester($command);

            $commandTester->execute(
                ['command' => $command->getName()],
                ['decorated' => false]
            );

            $output = $commandTester->getDisplay();
            $this->assertContains('Magento Location', $output, 'The command returned no Magento location.');
            $this->assertContains('App Version', $output, 'The command returned no app version');
        }
    }

    public function testAttributes()
    {
        $this->assertClassHasAttribute('input', InfoCommand::class, 'Class misses attribute');
        $this->assertClassHasAttribute('output', InfoCommand::class, 'Class misses attribute');
        $this->assertClassHasAttribute('fio', InfoCommand::class, 'Class misses attribute');
        $this->assertClassHasAttribute('mage', InfoCommand::class, 'Class misses attribute');
    }

    public function tearDown()
    {
        unset($this->command);
        parent::tearDown();
    }
}
