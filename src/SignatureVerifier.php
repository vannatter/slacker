<?php

declare(strict_types=1);

namespace Slacker;

/**
 * Verifies Slack request signatures (v0 scheme).
 *
 * @see https://api.slack.com/authentication/verifying-requests-from-slack
 */
final class SignatureVerifier
{
    /** Requests older than this are rejected to prevent replay attacks. */
    public const MAX_AGE_SECONDS = 300;

    /** @var \Closure(): int */
    private readonly \Closure $clock;

    /** @param (\Closure(): int)|null $clock */
    public function __construct(
        private readonly string $signingSecret,
        ?\Closure $clock = null,
    ) {
        $this->clock = $clock ?? time(...);
    }

    public function verify(string $timestamp, string $signature, string $body): bool
    {
        if ($this->signingSecret === '' || $signature === '' || !ctype_digit($timestamp)) {
            return false;
        }

        if (abs(($this->clock)() - (int) $timestamp) > self::MAX_AGE_SECONDS) {
            return false;
        }

        return hash_equals($this->sign($timestamp, $body), $signature);
    }

    public function sign(string $timestamp, string $body): string
    {
        return 'v0=' . hash_hmac('sha256', "v0:{$timestamp}:{$body}", $this->signingSecret);
    }
}
