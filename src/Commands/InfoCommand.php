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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InfoCommand.
 */
class InfoCommand extends AbstractCommand
{
    /**
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('info')
            ->setDescription('A general information command');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('mage')) {
            $this->nomage = true;
        }

        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \GDPRProof\Util\Patch\ParseException
     * @throws \InvalidArgumentException
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fio->title('Magento Patch Finder');

        $output->writeln('* <info>App Version: </info>'.$this->getApplication()->getVersion());

        if ($total = count($this->getPatches()->getPatches())) {
            $output->writeln('* <info>Patches indexed: </info>'.$total);
        }
        if ($lastIndexed = $this->getLastIndexed()) {
            $output->writeln('* <info>Last indexed: </info>'.$lastIndexed);
        }

        if ($this->getMage()->hasLoaded()) {
            $output->writeln('* <info>Magento Version: </info>'.$this->getMage()->getVersion());
            $output->writeln('* <info>Magento Edition: </info>'.$this->getMage()->getEdition());
            $output->writeln('* <info>Magento Location: </info>'.$this->getMage()->getRootDir());
        } else {
            $this->fio->warning('Magento was not found.');
        }
    }

    /**
     * @return string
     */
    protected function getLastIndexed()
    {
        return $this->getPatches()->getIndexTime()->format('Y-m-d\TH:i:sO');
    }
}
