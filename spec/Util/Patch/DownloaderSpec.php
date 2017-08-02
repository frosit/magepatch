<?php

namespace spec\Frosit\Util\Patch;

use Frosit\Util\Patch\Downloader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DownloaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Downloader::class);
    }
}
