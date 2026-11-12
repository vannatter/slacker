<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Http\HttpClient;
use Slacker\Reply;
use Slacker\Weather\OpenMeteo;

/**
 * Multi-day forecast (the old `weatherext` command).
 */
final class Forecast implements Plugin
{
    private const DAYS = 5;

    private readonly OpenMeteo $api;

    public function __construct(HttpClient $http, string $units = 'fahrenheit', string $country = '')
    {
        $this->api = new OpenMeteo($http, $units, $country);
    }

    public function name(): string
    {
        return 'weatherext';
    }

    public function usage(): string
    {
        return 'weatherext [zip code or city]';
    }

    public function handle(Command $command): Reply
    {
        if ($command->args === '') {
            return Reply::ephemeral("Where? Try `{$command->slashCommand} weatherext 10001`.");
        }

        $place = $this->api->geocode($command->args);
        if ($place === null) {
            return Reply::ephemeral("Couldn't find a place called \"" . Reply::escape($command->args) . '".');
        }

        $daily = $this->api->forecast($place, self::DAYS)['daily'] ?? null;
        if (!is_array($daily) || !is_array($daily['time'] ?? null)) {
            return Reply::ephemeral('Forecast data is unavailable right now.');
        }

        $unit = $this->api->unitSymbol();
        $lines = ['*' . self::DAYS . '-day forecast for ' . Reply::escape($place['name']) . '*'];

        foreach ($daily['time'] as $i => $date) {
            $code = (int) ($daily['weather_code'][$i] ?? -1);
            $lines[] = sprintf(
                '%s *%s* %d%s / %d%s, %s',
                OpenMeteo::emoji($code),
                (new \DateTimeImmutable((string) $date))->format('D'),
                round((float) ($daily['temperature_2m_max'][$i] ?? 0)),
                $unit,
                round((float) ($daily['temperature_2m_min'][$i] ?? 0)),
                $unit,
                OpenMeteo::describe($code),
            );
        }

        return Reply::channel(implode("\n", $lines));
    }
}
