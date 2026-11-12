<?php

declare(strict_types=1);

namespace Slacker\Http;

final class CurlHttpClient implements HttpClient
{
    /**
     * Slack expects a slash-command response within 3 seconds,
     * so outbound calls get a tight default budget.
     */
    public function __construct(
        private readonly float $timeoutSeconds = 2.5,
        private readonly string $userAgent = 'Slacker/3.0 (+https://github.com/vannatter/slacker)',
    ) {
    }

    public function getJson(string $url, array $query = []): array
    {
        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        [$status, $body] = $this->request('GET', $url);

        if ($status < 200 || $status >= 300) {
            throw new HttpException("HTTP {$status} from " . $this->redact($url));
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new HttpException('Invalid JSON from ' . $this->redact($url), 0, $e);
        }

        if (!is_array($data)) {
            throw new HttpException('Unexpected JSON from ' . $this->redact($url));
        }

        return $data;
    }

    public function post(string $url, string $body = ''): int
    {
        return $this->request('POST', $url, $body)[0];
    }

    /** @return array{int, string} */
    private function request(string $method, string $url, ?string $body = null): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new HttpException('Unable to initialise cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT_MS => (int) ($this->timeoutSeconds * 1000),
            CURLOPT_TIMEOUT_MS => (int) ($this->timeoutSeconds * 1000),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $result = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if (!is_string($result)) {
            $error = curl_error($ch);
            throw new HttpException("Request to {$this->redact($url)} failed: {$error}");
        }

        return [$status, $result];
    }

    /** Strip query strings so API keys never end up in logs. */
    private function redact(string $url): string
    {
        return strtok($url, '?') ?: $url;
    }
}
