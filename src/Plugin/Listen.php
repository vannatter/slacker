<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Http\HttpClient;
use Slacker\Reply;

/**
 * What a Last.fm user is (or was last) listening to.
 */
final class Listen implements Plugin
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $apiKey,
    ) {
    }

    public function name(): string
    {
        return 'listen';
    }

    public function usage(): string
    {
        return 'listen [last.fm username]';
    }

    public function handle(Command $command): Reply
    {
        if ($this->apiKey === '') {
            return Reply::ephemeral('Last.fm is not configured. Set `LASTFM_API_KEY` in `.env`.');
        }

        $user = $command->args;
        if ($user === '' || preg_match('/^[A-Za-z0-9_-]{2,32}$/', $user) !== 1) {
            return Reply::ephemeral("Give me a Last.fm username, e.g. `{$command->slashCommand} listen rj`.");
        }

        $data = $this->http->getJson('https://ws.audioscrobbler.com/2.0/', [
            'method' => 'user.getrecenttracks',
            'user' => $user,
            'api_key' => $this->apiKey,
            'format' => 'json',
            'limit' => 1,
        ]);

        if (isset($data['error'])) {
            return Reply::ephemeral(Reply::escape((string) ($data['message'] ?? 'Last.fm lookup failed.')));
        }

        $track = $data['recenttracks']['track'][0] ?? null;
        if (!is_array($track)) {
            return Reply::channel("{$user} hasn't scrobbled anything yet.");
        }

        $nowPlaying = ($track['@attr']['nowplaying'] ?? '') === 'true';

        return Reply::channel(sprintf(
            ':musical_note: %s %s *%s* by %s %s',
            $user,
            $nowPlaying ? 'is listening to' : 'last listened to',
            Reply::escape((string) ($track['name'] ?? 'something')),
            Reply::escape((string) ($track['artist']['#text'] ?? 'someone')),
            (string) ($track['url'] ?? ''),
        ));
    }
}
