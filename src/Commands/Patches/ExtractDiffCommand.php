<?php
/**
 *     Magepatch - Magento Patches finder & verification utility
 *
 *     @Copyright (c) 2017 Fabio Ros (FROSIT) <info@gdprproof.com> (https://gdprproof.com)
 *     @License GNU GPLv3  (http://www.gnu.org/licenses/gpl-3.0.txt)
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 */

namespace GDPRProof\Commands\Patches;

use GDPRProof\Commands\AbstractCommand;
use GDPRProof\Util\Patch\Diff;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ShowAppliedCommand
 * @package GDPRProof\Commands\Patches
 */
class ExtractDiffCommand extends AbstractCommand
{
    /**
     * @var string $baseDir
     */
    protected $baseDir;


    protected function configure()
    {
        $this->setName('patches:extract-diff')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to the patch file, defaults to var/patches',
                false
            )
            ->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Extract diffs recursively')
            ->setDescription('Extract the diff part off the patch file.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fio->title('Magento Patch Finder - Diff extractor');

        if (!$input->getArgument('path')) {
            $path = $this->getMage()->getRootDir().DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'patches';
        } else {

            // @todo what is this
            $path = $input->getArgument('path');
            if (!file_exists($path) || !is_readable($path)) {
                $this->fio->error('No path specified, add a custom path or specify magento\'s location');
                exit(0);
            }


            $files = new \DirectoryIterator($path);
            $patchFiles = [];

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if ($file->isFile() && substr($file->getFilename(), -3) === '.sh') {
                    $name = $file->getFilename();

                    if (file_exists(str_replace('.sh', '.diff', $file->getRealPath()))) {
                        continue;
                    }

                    $patchFiles[] = $name;
                }
            }


            if (count($patchFiles) <= 0) {
                $this->fio->writeln(
                    'Could not find any '.count(
                        $patchFiles
                    ).' patch files to extract from or that wasn\'t already extracted.'
                );
                exit(1);
            }

            $this->fio->writeln('Found '.count($patchFiles).' patch files that are not extracted.');

            if (!$input->getOption('recursive')) {
                if ($file = $this->fio->choice('Which file do you want to extract a diff from?', $patchFiles)) {
                    $toProcess[] = $file;
                } else {
                    exit(0);
                }
            } else {
                $toProcess = $patchFiles;
            }


            $diffFiles = [];
            foreach ($toProcess as $patchFile) {
                $diffFiles[] = $diffFile = Diff::extractDiff($patchFile, $path);
                $this->fio->writeUpdate('Converted Diff: ', $diffFile);
            }

            if (count($diffFiles > 1)) {
                $this->fio->section('Currently extracted:');
                $this->fio->listing($diffFiles);
            }

        }
    }
}
