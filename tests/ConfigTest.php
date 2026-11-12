<?php

declare(strict_types=1);

namespace Slacker\Tests;

use PHPUnit\Framework\TestCase;
use Slacker\Config;

final class ConfigTest extends TestCase
{
    public function testAccessors(): void
    {
        $config = new Config([
            'FLAG' => 'true',
            'OFF' => '0',
            'LIST' => ' U1, U2 ,,',
            'MAP' => 'Site=https://a/hook?x=1, api = https://b ,broken',
        ]);

        self::assertTrue($config->bool('FLAG'));
        self::assertFalse($config->bool('OFF', true));
        self::assertTrue($config->bool('MISSING', true));
        self::assertSame('fallback', $config->get('MISSING', 'fallback'));
        self::assertSame(['U1', 'U2'], $config->list('LIST'));
        self::assertSame(['site' => 'https://a/hook?x=1', 'api' => 'https://b'], $config->map('MAP'));
    }
}
