<?php

declare(strict_types=1);

namespace Slacker\Weather;

use Slacker\Http\HttpClient;

/**
 * Thin client for Open-Meteo (free, no API key). https://open-meteo.com/
 */
final class OpenMeteo
{
    public function __construct(
        private readonly HttpClient $http,
        /** "fahrenheit" or "celsius" */
        private readonly string $units = 'fahrenheit',
        /** Optional ISO-3166 alpha-2 country to bias lookups, e.g. "US". */
        private readonly string $country = '',
    ) {
    }

    /**
     * Resolve a zip code or place name.
     *
     * @return array{name: string, latitude: float, longitude: float}|null
     */
    public function geocode(string $query): ?array
    {
        $params = ['name' => $query, 'count' => 1, 'language' => 'en', 'format' => 'json'];
        if ($this->country !== '') {
            $params['countryCode'] = strtoupper($this->country);
        }

        $data = $this->http->getJson('https://geocoding-api.open-meteo.com/v1/search', $params);
        $place = $data['results'][0] ?? null;
        if (!is_array($place) || !isset($place['latitude'], $place['longitude'])) {
            return null;
        }

        $parts = array_filter([
            $place['name'] ?? null,
            $place['admin1'] ?? null,
            $place['country_code'] ?? null,
        ], static fn ($part): bool => is_string($part) && $part !== '');

        return [
            'name' => implode(', ', array_unique($parts)),
            'latitude' => (float) $place['latitude'],
            'longitude' => (float) $place['longitude'],
        ];
    }

    /**
     * @param array{latitude: float, longitude: float} $place
     * @return array<mixed>
     */
    public function forecast(array $place, int $days): array
    {
        return $this->http->getJson('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'current' => 'temperature_2m,apparent_temperature,weather_code',
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min',
            'temperature_unit' => $this->units === 'celsius' ? 'celsius' : 'fahrenheit',
            'timezone' => 'auto',
            'forecast_days' => $days,
        ]);
    }

    public function unitSymbol(): string
    {
        return $this->units === 'celsius' ? '°C' : '°F';
    }

    /** WMO weather interpretation codes. */
    public static function describe(int $code): string
    {
        return match (true) {
            $code === 0 => 'clear sky',
            $code === 1 => 'mainly clear',
            $code === 2 => 'partly cloudy',
            $code === 3 => 'overcast',
            in_array($code, [45, 48], true) => 'fog',
            in_array($code, [51, 53, 55], true) => 'drizzle',
            in_array($code, [56, 57], true) => 'freezing drizzle',
            in_array($code, [61, 63, 65], true) => 'rain',
            in_array($code, [66, 67], true) => 'freezing rain',
            in_array($code, [71, 73, 75], true) => 'snow',
            $code === 77 => 'snow grains',
            in_array($code, [80, 81, 82], true) => 'rain showers',
            in_array($code, [85, 86], true) => 'snow showers',
            $code === 95 => 'thunderstorm',
            in_array($code, [96, 99], true) => 'thunderstorm with hail',
            default => 'unknown conditions',
        };
    }

    public static function emoji(int $code): string
    {
        return match (true) {
            $code < 0 => ':grey_question:',
            $code <= 1 => ':sunny:',
            $code === 2 => ':partly_sunny:',
            $code === 3 => ':cloud:',
            $code < 50 => ':fog:',
            $code < 70 => ':rain_cloud:',
            $code < 80 => ':snowflake:',
            $code < 85 => ':rain_cloud:',
            $code < 90 => ':snowflake:',
            default => ':thunder_cloud_and_rain:',
        };
    }
}
