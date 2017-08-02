<?php

namespace spec\Frosit\Util\Patch;

use Frosit\Util\Patch\Diff;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DiffSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Diff::class);
    }
}
