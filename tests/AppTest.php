<?php

declare(strict_types=1);

namespace Slacker\Tests;

use PHPUnit\Framework\TestCase;
use Slacker\App;
use Slacker\Config;
use Slacker\HttpResponse;
use Slacker\SignatureVerifier;

final class AppTest extends TestCase
{
    private const SECRET = 'test-secret';

    /** @param array<string, string> $env */
    private function app(array $env = [], ?FakeHttpClient $http = null): App
    {
        return App::create(
            new Config($env + ['SLACK_SIGNING_SECRET' => self::SECRET]),
            $http ?? new FakeHttpClient(),
        );
    }

    /** @param array<string, string> $fields */
    private function send(App $app, array $fields, ?string $signature = null): HttpResponse
    {
        $body = http_build_query($fields + ['command' => '/bot', 'user_id' => 'U1', 'user_name' => 'dana']);
        $ts = (string) time();
        $signature ??= (new SignatureVerifier(self::SECRET))->sign($ts, $body);

        return $app->handle('POST', [
            'X-Slack-Request-Timestamp' => $ts,
            'X-Slack-Signature' => $signature,
        ], $body);
    }

    /** @return array<string, mixed> */
    private function json(HttpResponse $response): array
    {
        return json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testRejectsUnsignedRequests(): void
    {
        $response = $this->send($this->app(), ['text' => 'say hi'], 'v0=bogus');
        self::assertSame(401, $response->status);
    }

    public function testRejectsGet(): void
    {
        self::assertSame(405, $this->app()->handle('GET', [], '')->status);
    }

    public function testSayPostsInChannel(): void
    {
        $data = $this->json($this->send($this->app(), ['text' => 'say hello <!channel>']));

        self::assertSame('in_channel', $data['response_type']);
        self::assertSame('hello &lt;!channel&gt;', $data['text']);
    }

    public function testPrependUserMention(): void
    {
        $data = $this->json($this->send($this->app(['RESPONSE_PREPEND_USER' => 'true']), ['text' => 'say hi']));
        self::assertSame('<@U1>, hi', $data['text']);
    }

    public function testUnknownCommandIsEphemeral(): void
    {
        $data = $this->json($this->send($this->app(), ['text' => 'nope']));

        self::assertSame('ephemeral', $data['response_type']);
        self::assertStringContainsString('/bot help', $data['text']);
    }

    public function testEmptyTextShowsHelp(): void
    {
        $data = $this->json($this->send($this->app(), ['text' => '']));

        self::assertSame('ephemeral', $data['response_type']);
        self::assertStringContainsString('/bot weather', $data['text']);
        self::assertStringContainsString('/bot help', $data['text']);
    }

    public function testDisabledPluginsAreNotListedOrRunnable(): void
    {
        $app = $this->app(['SLACKER_DISABLED_PLUGINS' => 'say, push']);

        $help = $this->json($this->send($app, ['text' => 'help']));
        self::assertStringNotContainsString('/bot say', $help['text']);
        self::assertStringNotContainsString('/bot push', $help['text']);

        $say = $this->json($this->send($app, ['text' => 'say hi']));
        self::assertStringContainsString('Unknown command', $say['text']);
    }

    public function testPluginFailureReturnsFriendlyError(): void
    {
        $data = $this->json($this->send($this->app(), ['text' => 'pug']));

        self::assertSame('ephemeral', $data['response_type']);
        self::assertStringContainsString('`pug` failed', $data['text']);
    }

    public function testCommandIsCaseInsensitive(): void
    {
        $data = $this->json($this->send($this->app(), ['text' => 'SAY Hi']));
        self::assertSame('Hi', $data['text']);
    }
}
