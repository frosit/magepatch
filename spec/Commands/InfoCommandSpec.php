<?php

namespace spec\Frosit\Commands;

use Frosit\Commands\AbstractCommand;
use Frosit\Commands\InfoCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InfoCommand::class);
    }

    function extends_abstract_command(){
        $this->shouldBeAnInstanceOf(AbstractCommand::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('info');
    }

}
