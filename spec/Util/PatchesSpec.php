<?php

namespace spec\Frosit\Util;

use Frosit\Util\Patches;
use PhpSpec\ObjectBehavior;

class PatchesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(__DIR__.'/../../res/patches.json');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Patches::class);
    }
}
