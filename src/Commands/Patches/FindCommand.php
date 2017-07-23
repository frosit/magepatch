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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FindCommand.
 */
class FindCommand extends AbstractCommand
{
    protected $version;
    protected $edition;
    protected $baseDir;

    protected function configure()
    {
        $this->setName('patches:find')
            ->addOption('version', null, InputOption::VALUE_OPTIONAL, 'Specify a different version e.g. 1.7.0.1')
            ->addOption(
                'edition',
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify a different edition e.g. EE or enterprise'
            )
            ->setDescription('Automatically verify installed patch and find / download missing ones.')
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> command finds patches for a certain installation.
By default, it tries to find a Magento installation.


  <info>php %command.full_name%</info>

You can also output the information in other formats by using the <comment>--format</comment> option:

  <info>php %command.full_name% --format=xml</info>
EOF
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('nomage') && !$input->getOption('version' && !$input->getOption('edition'))) {
            $this->fio->writeln(
                'Specify a version and edition using --version and --edition like --version=1.9.2.1 --edition=ce'
            );
        }
        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Exception
     * @throws \GDPRProof\Util\Patch\ParseException
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fio->title('Magento Patch Finder');

        $this->fio->writeUpdate('Fetching Magento info...');
        if ($this->getMage() && $this->getMageInfo()) {
            $this->fio->writeUpdate('Magento found at:', $this->baseDir);
            $this->fio->writeUpdate('Loading patches...');

            $patches = $this->getPatches()->getPatchesForMage($this->getMage(), false);

            $this->fio->writeUpdate('Preparing patches...');
            if ($patches) {
                if ($appliedPatches = $this->getMage()->getAppliedPatches()) {
                    $this->fio->writeUpdate('Applied Patches: ', count($appliedPatches));
                } else {
                    $this->fio->comment('No patches we\'re applied or no applied.patches.list was found');
                }

                $this->showStatusTable($patches);

                if ($notApplied = $this->getPatches()->getPatchesForMage($this->getMage())) {
                    if (count($notApplied) > 0) {
                        $this->fio->warning(
                            count(
                                $notApplied
                            ).' patches were not applied or could not be matched based on their checksum'
                        );

                        if ($this->fio->confirm('Would you like to download the missing patches?')) {
                            $this->fio->writeln('Downloading patches...');
                            $this->getPatches()->downloadPatches($notApplied, $this->getMage()->getRootDir());
                            $this->fio->success('Patches downloaded');
                        }
                    }
                }
            }
        }
    }

    /**
     * Shows patch status table.
     *
     * @param $patches
     */
    protected function showStatusTable(array $patches)
    {
        $tablePatches = [];
        $allowedKeys = ['uid', 'supee', 'revision', 'checksum', 'applied'];
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

    /**
     * Get Magento info.
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function getMageInfo()
    {
        if ($this->getMage()->hasLoaded()) {
            $this->version = $this->getMage()->getVersion();
            $this->edition = $this->getMage()->getEdition();
            $this->baseDir = $this->getMage()->getRootDir();

            return true;
        }

        $this->fio->warning('Magento was not found.');

        return false;
    }
}
