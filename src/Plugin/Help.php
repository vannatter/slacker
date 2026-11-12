<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Reply;

/**
 * Lists the enabled commands. Only the invoking user sees it.
 */
final class Help implements Plugin
{
    public function __construct(private readonly PluginRegistry $registry)
    {
    }

    public function name(): string
    {
        return 'help';
    }

    public function usage(): string
    {
        return 'help';
    }

    public function handle(Command $command): Reply
    {
        $lines = array_map(
            static fn (Plugin $plugin): string => "• `{$command->slashCommand} {$plugin->usage()}`",
            array_values($this->registry->all()),
        );

        return Reply::ephemeral("*Available commands*\n" . implode("\n", $lines));
    }
}
