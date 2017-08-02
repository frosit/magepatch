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

namespace Frosit\Util;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class FrositIoHelper.
 */
class FrositIoHelper extends SymfonyStyle implements OutputInterface, StyleInterface
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var HelperSet
     */
    protected $helperSet;

    /**
     * @var BufferedOutput
     */
    private $bufferedOutput;

    /**
     * @var mixed
     */
    private $lineLength;

    /**
     * Sets the helper set associated with this helper.
     *
     * @param HelperSet $helperSet A HelperSet instance
     */
    public function setHelperSet(HelperSet $helperSet = null)
    {
        $this->helperSet = $helperSet;
    }

    /**
     * Gets the helper set associated with this helper.
     *
     * @return HelperSet A HelperSet instance
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * @var string
     */
    public static $logo = '
 ██████╗ ██████╗ ██████╗ ██████╗ ██████╗ ██████╗  ██████╗  ██████╗ ███████╗
██╔════╝ ██╔══██╗██╔══██╗██╔══██╗██╔══██╗██╔══██╗██╔═══██╗██╔═══██╗██╔════╝
██║  ███╗██║  ██║██████╔╝██████╔╝██████╔╝██████╔╝██║   ██║██║   ██║█████╗  
██║   ██║██║  ██║██╔═══╝ ██╔══██╗██╔═══╝ ██╔══██╗██║   ██║██║   ██║██╔══╝  
╚██████╔╝██████╔╝██║     ██║  ██║██║     ██║  ██║╚██████╔╝╚██████╔╝██║     
 ╚═════╝ ╚═════╝ ╚═╝     ╚═╝  ╚═╝╚═╝     ╚═╝  ╚═╝ ╚═════╝  ╚═════╝ ╚═╝                                                    
<blue>-------------------</blue>  <comment>Security & Compliancy company</comment>   <blue>------------------------</blue> 
<green>=============================================================================</green>
';

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'frosit-io';
    }

    /**
     * FrositStyleHelper constructor.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->input = $input;
        $this->output = $this->addOutputStyles($output);

        $this->bufferedOutput = new BufferedOutput($output->getVerbosity(), false, clone $output->getFormatter());
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $this->lineLength = min($this->getTerminalWidth() - (int) (DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);

        parent::__construct($input, $output);
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return self::$logo;
    }

    /**
     * @param $message
     *
     * @return $this
     */
    public function info($message)
    {
        $this->writeln('<info>'.$message.'</info>');

        return $this;
    }

    /**
     * A line that gets overridden by the next line.
     *
     * @param $message
     * @param null $value
     *
     * @return $this
     */
    public function writeUpdate($message, $value = null)
    {
        if ($value) {
            $this->writeln('* <blue>'.$message.'</blue>: '.$value);
        } else {
            $this->writeln('* <blue>'.$message.' </blue>');
        }

        return $this;
    }

    /**
     * @param array|string $messages
     * @param bool         $newline
     * @param int          $type
     *
     * @return $this
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        parent::write($messages, $newline, $type);
        $this->bufferedOutput->write($this->reduceBuffer($messages), $newline, $type);

        return $this;
    }

    /**
     * Error style.
     *
     * @param array|string $message
     *
     * @return $this
     */
    public function error($message)
    {
        $this->writeln('<error>'.$message.'</error>');

        return $this;
    }

    /**
     * Red style.
     *
     * @param  $message
     *
     * @return $this
     */
    public function red($message)
    {
        $this->writeln('<red>'.$message.'</red>');

        return $this;
    }

    /**
     * Processes an key => value array into single line with formatting and lists it.
     *
     * @param  $array
     * @param array $config
     *
     * @return mixed
     */
    public function listArrayKeyValue($array, array $config = [])
    {
        $newArr = [];
        $config['seperator'] = isset($config['seperator']) ?: ': ';
        $config['colorClass'] = isset($config['colorClass']) ?: 'info';

        foreach ($array as $key => $item) {
            if (is_string($item)) {
                $newArr[] = sprintf('<%s>', $config['colorClass']).$key.sprintf(
                        '</%s>',
                        $config['colorClass']
                    ).$config['seperator'].$item;
            }
        }

        $this->listing($newArr);

        return $array;
    }

    /**
     * Flatten array to dot notation.
     *
     * @param  $array
     * @param string $prefix
     *
     * @return array
     */
    public function flatten($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                /*
                 * @noinspection AdditionOperationOnArraysInspection
                 */
                $result += $this->flatten($value, $prefix.$key.'.');
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Empty console screen.
     *
     * @return $this
     */
    public function clearConsole()
    {
        $this->write(sprintf("\033\143"));

        return $this;
    }

    /**
     * @param null $subtext
     * @param int  $newlines
     *
     * @return $this
     */
    public function writeLogo($subtext = null, $newlines = 2)
    {
        $this->output->write(self::$logo);
        if ($subtext !== null) {
            $this->output->write($subtext);
        }
        if ($newlines > 0) {
            $this->newLine($newlines);
        }

        return $this;
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int          $type
     *
     * @internal param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return $this
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        parent::writeln($messages, $type);
        $this->bufferedOutput->writeln($this->reduceBuffer($messages), $type);

        return $this;
    }

    /**
     * Blank new line.
     *
     * @param int $count
     *
     * @return $this
     */
    public function newLine($count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(str_repeat("\n", $count));

        return $this;
    }

    /**
     * @param $messages
     *
     * @return array
     */
    private function reduceBuffer($messages)
    {
        // We need to know if the two last chars are PHP_EOL
        // Preserve the last 4 chars inserted (PHP_EOL on windows is two chars) in the history buffer
        return array_map(
            function ($value) {
                return mb_substr($value, -4);
            },
            array_merge([$this->bufferedOutput->fetch()], (array) $messages)
        );
    }

    /**
     * A slightly modifed multiple-choice.
     *
     * @todo   test
     *
     * @param  $question
     * @param array $choices
     * @param null  $default
     *
     * @return string
     */
    public function multichoice($question, array $choices, $default = null)
    {
        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default];
        }

        $choiceQuestion = new ChoiceQuestion($question, $choices, $default);
        $choiceQuestion->isMultiselect(true);

        return $this->askQuestion($choiceQuestion);
    }

    /**
     * @return int
     */
    private function getTerminalWidth()
    {
        $application = new Application();
        $dimensions = $application->getTerminalDimensions();

        return $dimensions[0] ?: self::MAX_LINE_LENGTH;
    }

    /**
     * Add a lot of different output styles.
     *
     * @param OutputInterface $output
     * @param bool            $generate
     *
     * @return OutputInterface
     *
     * @internal param array $options
     * @internal param bool $override
     */
    public function addOutputStyles(OutputInterface $output, $generate = true)
    {
        $output->getFormatter()->setStyle('awesome', new OutputFormatterStyle('blue', 'green'));

        // override default style
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('magenta', 'white'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'yellow', ['bold']));
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle('blue'));
        $output->getFormatter()->setStyle('comment', new OutputFormatterStyle('green'));

        // auto-generate styles
        if ($generate) {
            // common options
            $opts = ['bold', 'underscore'];

            // common colors
            $colors = ['blue', 'green', 'yellow', 'red', 'cyan', 'white', 'default'];

            /*
             * Each color will be a style, for example
             * <green>some green text</green>
             * <blue>some blue text</blue>
             */
            foreach ($colors as $color) {
                $output->getFormatter()->setStyle($color, new OutputFormatterStyle($color));
            }

            /*
             * Auto-generate colors with options by specified format.
             *
             * format: [[o]ption [color]] (first letter of option+color)
             *
             * options:
             *  - [b]old
             *  - [u]nderscore
             *
             *  formats:
             *  - bwhite (bold / green)
             *  - ugreen (underscore/green)
             *  - etc..
             *
             * @note Options cascade, example:
             *  - <bdefault><ugreen>Bold Underscore Green</ugreen></bdefault>
             *
             *  Useful in combination with the ->text() function,
             *  ables to write somewhat longer texts with accentuation, disclaimers / credits etc...
             */
            foreach ($colors as $color) {
                foreach ($opts as $opt) {
                    $prefix = mb_substr($opt, 0, 2);
                    $output->getFormatter()->setStyle(
                        $prefix.$color,
                        new OutputFormatterStyle($color, null, [$opt])
                    );
                }
            }

            /*
             * Auto mix&match color foregrounds with backgrounds
             *
             * - prefix = $color[0] (first letter) + background
             * - or
             * - postfix = $color + [b]ackground
             *
             *  Example:
             *  <gred>fg=green & bg=red</gred> === <greenr>gf=green & bg=red</greenr>
             */
            foreach ($colors as $fg) {
                foreach ($colors as $bg) {
                    if ($bg !== $fg) {
                        $fgp = $fg[0]; // foreground prefix
                        $output->getFormatter()->setStyle($fgp.$bg, new OutputFormatterStyle($fg, $bg));
                    }
                }
                foreach ($colors as $bg) {
                    if ($bg !== $fg) {
                        $bgp = $bg[0]; // foreground prefix
                        $output->getFormatter()->setStyle($fg.$bgp, new OutputFormatterStyle($fg, $bg));
                    }
                }
            }
        }

        return $output;
    }
}
