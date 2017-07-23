<?php
/**
 * Magepatch - Magento Patches finder & verification utility
 *
 * @Copyright (c) 2017 Fabio Ros (FROSIT) <info@frosit.nl> (https://frosit.nl)
 * @License GNU GPLv3  (http://www.gnu.org/licenses/gpl-3.0.txt)
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
 */

namespace GDPRProof\Commands\Patches;

use GDPRProof\Commands\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShowAppliedCommand.
 */
class ShowAppliedCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $baseDir;

    protected function configure()
    {
        $this->setName('patches:show-applied')
            ->addArgument('patchfile', InputArgument::OPTIONAL, 'Path of the applied.patches file', false)
            ->setDescription('Show installed patches using the applied file');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fio->title('Magento Patch Finder - Show Applied Patches');

        // @todo clean after test
        //        $patchUtil = new Patch();
        //
        //        $patchLocation = $input->getArgument('patchfile') ?: null;
        //
        //        if ($patchLocation !== null) {
        //            $this->fio->writeUpdate('Applied patches file set: '.$patchLocation);
        //        } else {
        //            if ($this->getMage()) {
        //                $this->baseDir = $this->getMage()->getRootDir();
        //                $patchUtil->setBaseDir($this->baseDir);
        //                $this->fio->writeUpdate('Found Magento at: '.$this->baseDir);
        //            }
        //        }

        if ($appliedPatches = $this->getMage()->getAppliedPatches()) {
            $this->showStatusTable($appliedPatches);
        } else {
            $this->fio->note('Could not parse applied patches');
        }
    }

    /**
     * Shows patch status table.
     *
     * @param $patches
     */
    protected function showStatusTable(
        $patches
    ) {
        $tablePatches = [];
        $allowedKeys = ['checksum', 'revision', 'version', 'supee', 'date'];

        foreach ($patches as $patch) {
            $tablePatch = [];
            foreach ($patch as $key => $value) {
                if (in_array($key, $allowedKeys, true)) {
                    $tablePatch[$key] = $value;
                }
            }
            $tablePatches[] = $tablePatch;
        }
        $this->fio->table($allowedKeys, $tablePatches);
    }
}
