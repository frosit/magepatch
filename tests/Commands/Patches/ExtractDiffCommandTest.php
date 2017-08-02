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

use Frosit\TestCase;

/**
 * Class ExtractDiffCommandTest.
 *
 * @todo clean and optimise
 * @todo add execute test
 */
class ExtractDiffCommandTest extends TestCase
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
        return $this->getApplication()->find('patches:extract-diff');
    }

    public function testName()
    {
        $this->assertSame('patches:extract-diff', $this->getCommand()->getName());
    }

    public function testOptions()
    {
        $command = $this->getCommand();
        $this->assertSame(
            'recursive',
            $command->getDefinition()
                ->getOption('recursive')->getName()
        );
    }

    public function testAttributes()
    {
        $this->assertClassHasAttribute('version', FindCommand::class);
        $this->assertClassHasAttribute('edition', FindCommand::class);
        $this->assertClassHasAttribute('baseDir', FindCommand::class);
    }
}
