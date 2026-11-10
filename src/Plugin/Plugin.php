<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Reply;

interface Plugin
{
    /** The word users type after the slash command, e.g. "weather". */
    public function name(): string;

    /** Usage shown by `help`, without the slash command, e.g. "weather [zip or city]". */
    public function usage(): string;

    public function handle(Command $command): Reply;
}
