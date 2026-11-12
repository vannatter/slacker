<?php

declare(strict_types=1);

namespace Slacker;

use Dotenv\Dotenv;

/**
 * Read-only view over environment configuration.
 */
final class Config
{
    /** @param array<string, string> $values */
    public function __construct(private readonly array $values)
    {
    }

    /**
     * Loads `.env` from $root if present, then reads the real environment.
     * Real environment variables take precedence over `.env`.
     */
    public static function load(string $root): self
    {
        if (is_file($root . '/.env')) {
            Dotenv::createImmutable($root)->safeLoad();
        }

        $values = [];
        foreach (array_merge($_ENV, getenv()) as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $values[$key] = $value;
            }
        }

        return new self($values);
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->values[$key] ?? '';
        return $value === '' ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        if ($value === '') {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }

    /**
     * Comma-separated list, e.g. "U123,U456".
     *
     * @return list<string>
     */
    public function list(string $key): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->get($key)))));
    }

    /**
     * Comma-separated key=value map, e.g. "site=https://a,api=https://b".
     *
     * @return array<string, string>
     */
    public function map(string $key): array
    {
        $map = [];
        foreach ($this->list($key) as $pair) {
            [$name, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $name = trim($name);
            $value = trim($value);
            if ($name !== '' && $value !== '') {
                $map[strtolower($name)] = $value;
            }
        }
        return $map;
    }
}
