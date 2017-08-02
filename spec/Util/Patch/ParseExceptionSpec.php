<?php

namespace spec\Frosit\Util\Patch;

use Frosit\Util\Patch\ParseException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParseExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParseException::class);
    }
}
