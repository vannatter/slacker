<?php

declare(strict_types=1);

namespace Slacker;

use Slacker\Http\CurlHttpClient;
use Slacker\Http\HttpClient;
use Slacker\Plugin\PluginRegistry;

final class App
{
    public function __construct(
        private readonly SignatureVerifier $verifier,
        private readonly PluginRegistry $plugins,
        private readonly bool $prependUser = false,
        private readonly bool $debug = false,
    ) {
    }

    /**
     * Wires up the default plugin set from configuration.
     * Disable a plugin with SLACKER_DISABLED_PLUGINS=push,say
     */
    public static function create(Config $config, ?HttpClient $http = null): self
    {
        $http ??= new CurlHttpClient();
        $registry = new PluginRegistry();

        $all = [
            new Plugin\Say(),
            new Plugin\Holiday(),
            new Plugin\Countdown(
                $config->get('COUNTDOWN_DATE'),
                $config->get('COUNTDOWN_LABEL', 'the big day'),
                $config->get('COUNTDOWN_TIMEZONE', 'UTC'),
            ),
            new Plugin\Dog('pug', 'pug', 'here\'s a random pug!', $http),
            new Plugin\Dog('corgi', 'pembroke', 'here\'s a random corgi!', $http),
            new Plugin\Weather($http, $config->get('WEATHER_UNITS', 'fahrenheit'), $config->get('WEATHER_COUNTRY')),
            new Plugin\Forecast($http, $config->get('WEATHER_UNITS', 'fahrenheit'), $config->get('WEATHER_COUNTRY')),
            new Plugin\Listen($http, $config->get('LASTFM_API_KEY')),
            new Plugin\Push($http, $config->map('PUSH_REPOS'), $config->list('PUSH_ALLOWED_USERS')),
        ];

        $disabled = array_map('strtolower', $config->list('SLACKER_DISABLED_PLUGINS'));
        foreach ($all as $plugin) {
            if (!in_array($plugin->name(), $disabled, true)) {
                $registry->add($plugin);
            }
        }
        $registry->add(new Plugin\Help($registry));

        return new self(
            new SignatureVerifier($config->get('SLACK_SIGNING_SECRET')),
            $registry,
            $config->bool('RESPONSE_PREPEND_USER'),
            $config->bool('SLACKER_DEBUG'),
        );
    }

    /** @param array<string, string> $headers */
    public function handle(string $method, array $headers, string $body): HttpResponse
    {
        if (strtoupper($method) !== 'POST') {
            return HttpResponse::json(['error' => 'method not allowed'], 405);
        }

        $verified = $this->verifier->verify(
            $headers['X-Slack-Request-Timestamp'] ?? '',
            $headers['X-Slack-Signature'] ?? '',
            $body,
        );
        if (!$verified) {
            return HttpResponse::json(['error' => 'invalid signature'], 401);
        }

        parse_str($body, $payload);
        $command = Command::fromPayload($payload);

        if ($command->name === '') {
            $command = Command::fromPayload(['text' => 'help'] + $payload);
        }

        $plugin = $this->plugins->get($command->name);
        if ($plugin === null) {
            return $this->reply(
                Reply::ephemeral(sprintf(
                    'Unknown command `%s`. Try `%s help`.',
                    Reply::escape($command->name),
                    $command->slashCommand,
                )),
                $command,
            );
        }

        try {
            $reply = $plugin->handle($command);
        } catch (\Throwable $e) {
            error_log(sprintf('[slacker] %s failed: %s', $plugin->name(), $e->getMessage()));
            $reply = Reply::ephemeral(
                $this->debug
                    ? 'Error: ' . Reply::escape($e->getMessage())
                    : sprintf('Sorry, `%s` failed. Please try again later.', $plugin->name()),
            );
        }

        return $this->reply($reply, $command);
    }

    private function reply(Reply $reply, Command $command): HttpResponse
    {
        $prefix = ($this->prependUser && $reply->public) ? $command->mention() . ', ' : '';
        return HttpResponse::json($reply->toPayload($prefix));
    }
}
