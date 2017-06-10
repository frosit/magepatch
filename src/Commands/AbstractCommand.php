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

namespace GDPRProof\Commands;

use GDPRProof\Application;
use GDPRProof\Util\FrositIoHelper;
use GDPRProof\Util\Mage;
use GDPRProof\Util\Patches;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\FileTypeFilterIterator;

/**
 * Class AbstractCommand
 * @package GDPRProof\Commands
 */
abstract class AbstractCommand extends Command
{

    /**
     * @var InputInterface $input
     */
    public $input;

    /**
     * @var OutputInterface $output
     */
    public $output;

    /** @var  FrositIoHelper */
    public $fio;

    /**
     * @var Mage $mage
     */
    public $mage;

    /**
     * @var
     */
    public $nomage;

    /**
     * @var Patches
     */
    public $patches;

    /**
     * AbstractCommand constructor.
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct()
    {
        parent::__construct();
        $this->setGenericOptions();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \InvalidArgumentException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->input) {
            $this->input = $input;
        }
        if (null === $this->output) {
            $this->output = $output;
        }

        $this->fio = $this->getFrositIOHelper();
        $this->nomage = $input->getOption('nomage');
        $this->mage = $this->getApplication()->getMage();

        // Override edition
        if ($input->getOption('edition')) {
            $this->mage->setEdition($input->getOption('edition'));
        }

        // override version
        if ($input->getOption('version')) {
            $this->mage->setVersion($input->getOption('version'));
        }

        // Force location (basedir or Mage.php)
        if ($input->getOption('mage')) {
            $this->mage->find($input->getOption('mage'));
        }

        parent::initialize($input, $output);
    }

    /**
     * @return FrositIoHelper
     */
    protected function getFrositIOHelper()
    {
        /** @var FrositIoHelper */
        return new FrositIoHelper($this->input, $this->output);
    }

    /**
     * Get patches collection
     *
     * @return Patches
     */
    public function getPatches()
    {
        if ($this->patches === null) {
            $this->patches = new Patches(
                $this->getApplication()->getRootDir().DIRECTORY_SEPARATOR.'res'.DIRECTORY_SEPARATOR.'patches.json'
            ); // @todo verify
        }

        return $this->patches;
    }

    /**
     * Gets Mage proxy
     * @return Mage
     */
    public function getMage()
    {
        if (!$this->mage->hasLoaded()) {
            $this->mage->find();
        }

        return $this->mage;
    }

    /**
     * Sets generic command options
     */
    public function setGenericOptions()
    {
        $this->addOption('mage', null, InputOption::VALUE_OPTIONAL, 'Path to Magento\'s root directory.');
        $this->addOption('nomage', null, InputOption::VALUE_NONE, 'Do not look for Magento');
        $this->addOption('edition', null, InputOption::VALUE_OPTIONAL, 'Override edition (ee or ce)');
        $this->addOption('version', null, InputOption::VALUE_OPTIONAL, 'Override version (1.7.0.1)');
    }


    /**
     * @return Application|\Symfony\Component\Console\Application
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
}
