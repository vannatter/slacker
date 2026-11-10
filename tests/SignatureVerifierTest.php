<?php

declare(strict_types=1);

namespace Slacker\Tests;

use PHPUnit\Framework\TestCase;
use Slacker\SignatureVerifier;

final class SignatureVerifierTest extends TestCase
{
    private const NOW = 1_700_000_000;

    private function verifier(string $secret = 'secret'): SignatureVerifier
    {
        return new SignatureVerifier($secret, static fn (): int => self::NOW);
    }

    public function testMatchesSlackDocumentationExample(): void
    {
        // Example from https://api.slack.com/authentication/verifying-requests-from-slack
        $secret = '8f742231b10e8888abcd99yyyzzz85a5';
        $timestamp = '1531420618';
        $body = 'token=xyzz0WbapA4vBCDEFasx0q6G&team_id=T1DC2JH3J&team_domain=testteamnow&channel_id=G8PSS9T3V&channel_name=foobar&user_id=U2CERLKJA&user_name=roadrunner&command=%2Fwebhook-collect&text=&response_url=https%3A%2F%2Fhooks.slack.com%2Fcommands%2FT1DC2JH3J%2F397700885554%2F96rGlfmibIGlgcZRskXaIFfN&trigger_id=398738663015.47445629121.803a0bc887a14d10d2c447fce8b6703c';

        $verifier = new SignatureVerifier($secret, static fn (): int => 1531420618);

        self::assertTrue($verifier->verify(
            $timestamp,
            'v0=a2114d57b48eac39b9ad189dd8316235a7b4a8d21a10bd27519666489c69b503',
            $body,
        ));
    }

    public function testAcceptsValidSignature(): void
    {
        $v = $this->verifier();
        $ts = (string) self::NOW;
        self::assertTrue($v->verify($ts, $v->sign($ts, 'body'), 'body'));
    }

    public function testRejectsTamperedBody(): void
    {
        $v = $this->verifier();
        $ts = (string) self::NOW;
        self::assertFalse($v->verify($ts, $v->sign($ts, 'body'), 'body2'));
    }

    public function testRejectsStaleTimestamp(): void
    {
        $v = $this->verifier();
        $ts = (string) (self::NOW - SignatureVerifier::MAX_AGE_SECONDS - 1);
        self::assertFalse($v->verify($ts, $v->sign($ts, 'body'), 'body'));
    }

    public function testRejectsWhenSecretMissing(): void
    {
        $v = $this->verifier('');
        $ts = (string) self::NOW;
        self::assertFalse($v->verify($ts, $v->sign($ts, 'body'), 'body'));
    }

    public function testRejectsMalformedTimestamp(): void
    {
        self::assertFalse($this->verifier()->verify('abc', 'v0=00', 'body'));
    }
}
