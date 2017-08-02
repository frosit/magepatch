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

use Frosit\Commands\AbstractCommand;
use Frosit\Util\Patch\Diff;
use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

ini_set('display_errors', 1);

/**
 * Class ShowAppliedCommand.
 */
class ExtractDiffCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $baseDir;

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \InvalidArgumentException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int|null|void
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
        }
        $files = new \DirectoryIterator($path);
        $patchFiles = [];

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile() && mb_substr($file->getFilename(), -3) === '.sh') {
                $name = $file->getFilename();

                // Check for an already existing diff in that location
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
                ).' patch files to extract from or that were not already extracted.'
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

        if (count($diffFiles) > 1) {
            $this->fio->section('Currently extracted:');
            $this->fio->listing($diffFiles);
        }
    }
}
