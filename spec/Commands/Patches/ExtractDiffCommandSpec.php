<?php

namespace spec\Frosit\Commands\Patches;

use Frosit\Commands\AbstractCommand;
use Frosit\Commands\Patches\ExtractDiffCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExtractDiffCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExtractDiffCommand::class);
    }
    function extends_abstract_command(){
        $this->shouldBeAnInstanceOf(AbstractCommand::class);
    }
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('patches:extract-diff');
    }
}
