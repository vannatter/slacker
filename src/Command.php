<?php

declare(strict_types=1);

namespace Slacker;

/**
 * A parsed slash-command invocation, e.g. `/bot weather 10001`.
 */
final class Command
{
    public function __construct(
        /** Plugin name, lower-cased: "weather". */
        public readonly string $name,
        /** Everything after the plugin name: "10001". */
        public readonly string $args,
        /** The slash command itself: "/bot". */
        public readonly string $slashCommand,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $channelId,
        public readonly string $teamId,
    ) {
    }

    /** @param array<string, mixed> $payload Form fields Slack posts for a slash command. */
    public static function fromPayload(array $payload): self
    {
        $field = static fn (string $key): string => is_string($payload[$key] ?? null) ? trim($payload[$key]) : '';

        $parts = preg_split('/\s+/', $field('text'), 2) ?: [''];

        return new self(
            name: strtolower($parts[0]),
            args: trim($parts[1] ?? ''),
            slashCommand: $field('command') ?: '/bot',
            userId: $field('user_id'),
            userName: $field('user_name'),
            channelId: $field('channel_id'),
            teamId: $field('team_id'),
        );
    }

    /** Slack mention markup for the invoking user. */
    public function mention(): string
    {
        return $this->userId !== '' ? "<@{$this->userId}>" : '@' . $this->userName;
    }
}
