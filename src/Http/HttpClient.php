<?php

declare(strict_types=1);

namespace Slacker\Http;

interface HttpClient
{
    /**
     * GET a URL and decode the JSON body.
     *
     * @param array<string, scalar> $query
     * @return array<mixed>
     * @throws HttpException on transport errors, non-2xx status or invalid JSON
     */
    public function getJson(string $url, array $query = []): array;

    /**
     * POST to a URL (used for deploy hooks). Returns the HTTP status code.
     *
     * @throws HttpException on transport errors
     */
    public function post(string $url, string $body = ''): int;
}
