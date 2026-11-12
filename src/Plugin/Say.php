<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Reply;

/**
 * Echo text back into the channel.
 */
final class Say implements Plugin
{
    public function name(): string
    {
        return 'say';
    }

    public function usage(): string
    {
        return 'say [text]';
    }

    public function handle(Command $command): Reply
    {
        if ($command->args === '') {
            return Reply::ephemeral("What should I say? Try `{$command->slashCommand} say hello`.");
        }

        return Reply::channel(Reply::escape($command->args));
    }
}
