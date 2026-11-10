<?php

declare(strict_types=1);

namespace Slacker\Tests;

use Slacker\Http\HttpClient;
use Slacker\Http\HttpException;

/**
 * Canned responses keyed by URL prefix; records every request.
 */
final class FakeHttpClient implements HttpClient
{
    /** @var list<array{method: string, url: string, query: array<string, scalar>}> */
    public array $requests = [];

    /**
     * @param array<string, array<mixed>|HttpException> $json URL prefix => decoded JSON (or exception to throw)
     */
    public function __construct(
        private array $json = [],
        private int $postStatus = 200,
    ) {
    }

    public function getJson(string $url, array $query = []): array
    {
        $this->requests[] = ['method' => 'GET', 'url' => $url, 'query' => $query];

        foreach ($this->json as $prefix => $response) {
            if (str_starts_with($url, $prefix)) {
                if ($response instanceof HttpException) {
                    throw $response;
                }
                return $response;
            }
        }

        throw new HttpException("No fake response for {$url}");
    }

    public function post(string $url, string $body = ''): int
    {
        $this->requests[] = ['method' => 'POST', 'url' => $url, 'query' => []];
        return $this->postStatus;
    }
}
