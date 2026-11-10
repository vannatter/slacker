<?php

declare(strict_types=1);

namespace Slacker\Tests\Plugin;

use PHPUnit\Framework\TestCase;
use Slacker\Command;
use Slacker\Plugin\Countdown;
use Slacker\Plugin\Dog;
use Slacker\Plugin\Forecast;
use Slacker\Plugin\Holiday;
use Slacker\Plugin\Listen;
use Slacker\Plugin\Push;
use Slacker\Plugin\Weather;
use Slacker\Tests\FakeHttpClient;

final class PluginTest extends TestCase
{
    private const GEOCODE = 'https://geocoding-api.open-meteo.com/';
    private const FORECAST = 'https://api.open-meteo.com/';

    private static function cmd(string $text, string $userId = 'U1'): Command
    {
        return Command::fromPayload(['text' => $text, 'command' => '/bot', 'user_id' => $userId]);
    }

    private static function geocodeResult(): array
    {
        return ['results' => [[
            'name' => 'New York', 'admin1' => 'New York', 'country_code' => 'US',
            'latitude' => 40.71, 'longitude' => -74.0,
        ]]];
    }

    public function testHolidayHasAnEntryForEveryDayOfALeapYear(): void
    {
        $day = new \DateTimeImmutable('2028-01-01');
        for ($i = 0; $i < 366; $i++) {
            $date = $day->modify("+{$i} days");
            $reply = (new Holiday(static fn () => $date))->handle(self::cmd('holiday'));
            self::assertStringStartsWith('Happy ', $reply->text, $date->format('Y-m-d'));
        }
        $reply = (new Holiday(static fn () => new \DateTimeImmutable('2026-12-31')))->handle(self::cmd('holiday'));
        self::assertSame('Happy Unlucky Day!', $reply->text);
    }

    public function testCountdown(): void
    {
        $now = static fn () => new \DateTimeImmutable('2027-12-15 22:00', new \DateTimeZone('UTC'));
        $plugin = new Countdown('2027-12-17 00:00', 'the movie', 'UTC', $now);

        self::assertSame(':clock4: 1 day and 2 hours until the movie!', $plugin->handle(self::cmd('countdown'))->text);

        $past = new Countdown('2015-12-18', 'Episode VII', 'UTC', $now);
        self::assertStringContainsString('is here', $past->handle(self::cmd('countdown'))->text);

        self::assertFalse((new Countdown('', 'x'))->handle(self::cmd('countdown'))->public);
    }

    public function testDogReturnsImage(): void
    {
        $http = new FakeHttpClient(['https://dog.ceo/api/breed/pembroke/' => [
            'status' => 'success', 'message' => 'https://images.dog.ceo/breeds/pembroke/x.jpg',
        ]]);

        $reply = (new Dog('corgi', 'pembroke', 'corgi!', $http))->handle(self::cmd('corgi'));

        self::assertTrue($reply->public);
        self::assertSame('https://images.dog.ceo/breeds/pembroke/x.jpg', $reply->imageUrl);
        self::assertSame('image', $reply->toPayload()['blocks'][1]['type']);
    }

    public function testDogRejectsNonHttpsUrls(): void
    {
        $http = new FakeHttpClient(['https://dog.ceo/' => ['status' => 'success', 'message' => 'javascript:alert(1)']]);
        self::assertNull((new Dog('pug', 'pug', 'pug!', $http))->handle(self::cmd('pug'))->imageUrl);
    }

    public function testWeather(): void
    {
        $http = new FakeHttpClient([
            self::GEOCODE => self::geocodeResult(),
            self::FORECAST => ['current' => ['temperature_2m' => 58.7, 'apparent_temperature' => 52.4, 'weather_code' => 0]],
        ]);

        $reply = (new Weather($http, 'fahrenheit', 'us'))->handle(self::cmd('weather 10001'));

        self::assertSame(':sunny: Currently clear sky and 59°F (feels like 52°F) in New York, US', $reply->text);
        self::assertSame('10001', $http->requests[0]['query']['name']);
        self::assertSame('US', $http->requests[0]['query']['countryCode']);
    }

    public function testWeatherUnknownPlace(): void
    {
        $http = new FakeHttpClient([self::GEOCODE => ['generationtime_ms' => 0.1]]);
        $reply = (new Weather($http))->handle(self::cmd('weather nowhere'));

        self::assertFalse($reply->public);
        self::assertStringContainsString('nowhere', $reply->text);
    }

    public function testForecast(): void
    {
        $http = new FakeHttpClient([
            self::GEOCODE => self::geocodeResult(),
            self::FORECAST => ['daily' => [
                'time' => ['2026-11-10', '2026-11-11'],
                'weather_code' => [3, 63],
                'temperature_2m_max' => [63.3, 62.4],
                'temperature_2m_min' => [43.1, 50.0],
            ]],
        ]);

        $reply = (new Forecast($http, 'celsius'))->handle(self::cmd('weatherext new york'));

        self::assertStringContainsString(':cloud: *Tue* 63°C / 43°C, overcast', $reply->text);
        self::assertStringContainsString(':rain_cloud: *Wed* 62°C / 50°C, rain', $reply->text);
        self::assertSame('celsius', $http->requests[1]['query']['temperature_unit']);
    }

    public function testListenNowPlaying(): void
    {
        $http = new FakeHttpClient(['https://ws.audioscrobbler.com/' => ['recenttracks' => ['track' => [[
            'name' => 'Song', 'artist' => ['#text' => 'Band'], 'url' => 'https://last.fm/x', '@attr' => ['nowplaying' => 'true'],
        ]]]]]);

        $reply = (new Listen($http, 'key'))->handle(self::cmd('listen rj'));

        self::assertSame(':musical_note: rj is listening to *Song* by Band https://last.fm/x', $reply->text);
    }

    public function testListenValidatesUsernameAndKey(): void
    {
        $http = new FakeHttpClient();
        self::assertStringContainsString('LASTFM_API_KEY', (new Listen($http, ''))->handle(self::cmd('listen rj'))->text);
        self::assertFalse((new Listen($http, 'key'))->handle(self::cmd('listen <bad user>'))->public);
        self::assertSame([], $http->requests);
    }

    public function testPushRequiresAllowedUser(): void
    {
        $http = new FakeHttpClient();
        $push = new Push($http, ['site' => 'https://deploy.example/hook'], ['U_ADMIN']);

        $reply = $push->handle(self::cmd('push site', 'U_RANDOM'));

        self::assertFalse($reply->public);
        self::assertSame([], $http->requests);
    }

    public function testPushTriggersHook(): void
    {
        $http = new FakeHttpClient();
        $push = new Push($http, ['site' => 'https://deploy.example/hook'], ['U_ADMIN']);

        $reply = $push->handle(self::cmd('push Site', 'U_ADMIN'));

        self::assertStringContainsString('triggered a deploy of *site*', $reply->text);
        self::assertSame('https://deploy.example/hook', $http->requests[0]['url']);
    }

    public function testPushUnknownRepo(): void
    {
        $push = new Push(new FakeHttpClient(), ['site' => 'https://x'], ['U1']);
        self::assertStringContainsString('Known repos: site', $push->handle(self::cmd('push api'))->text);
    }
}
