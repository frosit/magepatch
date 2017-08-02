<?php

namespace spec\Frosit\Commands\Patches;

use Frosit\Commands\AbstractCommand;
use Frosit\Commands\Patches\ShowAppliedCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ShowAppliedCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ShowAppliedCommand::class);
    }
    function extends_abstract_command(){
        $this->shouldBeAnInstanceOf(AbstractCommand::class);
    }
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('patches:show-applied');
    }
}
