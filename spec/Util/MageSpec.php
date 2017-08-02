<?php

namespace spec\Frosit\Util;

use Frosit\Util\Mage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Mage::class);
    }
}
