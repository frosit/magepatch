<?php

namespace spec\Frosit;

use Frosit\Application;
use Frosit\Util\Mage;
use PhpSpec\ObjectBehavior;

class ApplicationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Application::class);
    }

    function it_extends_base_class()
    {
        $this->shouldBeAnInstanceOf(\Symfony\Component\Console\Application::class);
    }
}
