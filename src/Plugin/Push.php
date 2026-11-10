<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Http\HttpClient;
use Slacker\Reply;

/**
 * Trigger a deploy hook.
 *
 * PUSH_REPOS="site=https://deploy.example.com/hook/abc,api=https://..."
 * PUSH_ALLOWED_USERS="U012ABC,U034DEF"   (Slack user IDs; required)
 */
final class Push implements Plugin
{
    /**
     * @param array<string, string> $repos name => deploy hook URL
     * @param list<string> $allowedUsers Slack user IDs permitted to deploy
     */
    public function __construct(
        private readonly HttpClient $http,
        private readonly array $repos,
        private readonly array $allowedUsers,
    ) {
    }

    public function name(): string
    {
        return 'push';
    }

    public function usage(): string
    {
        return 'push [repo name]';
    }

    public function handle(Command $command): Reply
    {
        if (!in_array($command->userId, $this->allowedUsers, true)) {
            return Reply::ephemeral("You're not allowed to deploy. Ask an admin to add you to `PUSH_ALLOWED_USERS`.");
        }

        $repo = strtolower($command->args);
        $url = $this->repos[$repo] ?? null;
        if ($url === null) {
            $known = $this->repos === [] ? 'none configured' : implode(', ', array_keys($this->repos));
            return Reply::ephemeral('Unknown repo "' . Reply::escape($repo) . "\". Known repos: {$known}.");
        }

        $status = $this->http->post($url);
        if ($status < 200 || $status >= 300) {
            return Reply::channel(":x: Deploy hook for *{$repo}* failed (HTTP {$status}).");
        }

        return Reply::channel(":rocket: {$command->mention()} triggered a deploy of *{$repo}*.");
    }
}
