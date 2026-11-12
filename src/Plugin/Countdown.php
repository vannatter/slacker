<?php

declare(strict_types=1);

namespace Slacker\Plugin;

use Slacker\Command;
use Slacker\Reply;

/**
 * Countdown to a configured date (replaces the old Star Wars 2015 countdown).
 *
 * COUNTDOWN_DATE="2027-12-17 00:00"  COUNTDOWN_LABEL="the next Star Wars movie"
 */
final class Countdown implements Plugin
{
    /** @var \Closure(): \DateTimeImmutable */
    private readonly \Closure $clock;

    /** @param (\Closure(): \DateTimeImmutable)|null $clock */
    public function __construct(
        private readonly string $date,
        private readonly string $label,
        private readonly string $timezone = 'UTC',
        ?\Closure $clock = null,
    ) {
        $this->clock = $clock ?? static fn (): \DateTimeImmutable => new \DateTimeImmutable();
    }

    public function name(): string
    {
        return 'countdown';
    }

    public function usage(): string
    {
        return 'countdown';
    }

    public function handle(Command $command): Reply
    {
        if ($this->date === '') {
            return Reply::ephemeral('No countdown is configured. Set `COUNTDOWN_DATE` in `.env`.');
        }

        $target = new \DateTimeImmutable($this->date, new \DateTimeZone($this->timezone));
        $seconds = $target->getTimestamp() - ($this->clock)()->getTimestamp();

        if ($seconds <= 0) {
            return Reply::channel("{$this->label} is here! :tada:");
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);

        return Reply::channel(sprintf(
            ':clock4: %d %s and %d %s until %s!',
            $days,
            $days === 1 ? 'day' : 'days',
            $hours,
            $hours === 1 ? 'hour' : 'hours',
            $this->label,
        ));
    }
}
