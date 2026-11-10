<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Http\HttpClient;
use Slacker\Reply;

/**
 * Random dog picture from the Dog CEO API (https://dog.ceo/dog-api/).
 * Powers both `pug` and `corgi`.
 */
final class Dog implements Plugin
{
    public function __construct(
        private readonly string $command,
        private readonly string $breed,
        private readonly string $message,
        private readonly HttpClient $http,
    ) {
    }

    public function name(): string
    {
        return $this->command;
    }

    public function usage(): string
    {
        return $this->command;
    }

    public function handle(Command $command): Reply
    {
        $data = $this->http->getJson('https://dog.ceo/api/breed/' . rawurlencode($this->breed) . '/images/random');
        $url = $data['message'] ?? null;

        if (($data['status'] ?? '') !== 'success' || !is_string($url) || !str_starts_with($url, 'https://')) {
            return Reply::ephemeral("Couldn't find a {$this->command} right now. :sob:");
        }

        return Reply::channel(":dog: {$this->message}", $url);
    }
}
