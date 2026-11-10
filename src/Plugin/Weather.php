<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Http\HttpClient;
use Slacker\Reply;
use Slacker\Weather\OpenMeteo;

/**
 * Current conditions for a zip code or place name.
 */
final class Weather implements Plugin
{
    private readonly OpenMeteo $api;

    public function __construct(HttpClient $http, string $units = 'fahrenheit', string $country = '')
    {
        $this->api = new OpenMeteo($http, $units, $country);
    }

    public function name(): string
    {
        return 'weather';
    }

    public function usage(): string
    {
        return 'weather [zip code or city]';
    }

    public function handle(Command $command): Reply
    {
        if ($command->args === '') {
            return Reply::ephemeral("Where? Try `{$command->slashCommand} weather 10001`.");
        }

        $place = $this->api->geocode($command->args);
        if ($place === null) {
            return Reply::ephemeral("Couldn't find a place called \"" . Reply::escape($command->args) . '".');
        }

        $current = $this->api->forecast($place, 1)['current'] ?? null;
        if (!is_array($current)) {
            return Reply::ephemeral('Weather data is unavailable right now.');
        }

        $code = (int) ($current['weather_code'] ?? -1);
        $unit = $this->api->unitSymbol();

        return Reply::channel(sprintf(
            '%s Currently %s and %d%s (feels like %d%s) in %s',
            OpenMeteo::emoji($code),
            OpenMeteo::describe($code),
            round((float) ($current['temperature_2m'] ?? 0)),
            $unit,
            round((float) ($current['apparent_temperature'] ?? 0)),
            $unit,
            Reply::escape($place['name']),
        ));
    }
}
