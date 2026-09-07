<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * One calendar month, which is TWC's billing period.
 *
 * Every sponsored seat is invoiced on the last day of the month, so cycles
 * never depend on when an employee happened to join. Seats that start
 * mid-month are prorated by the days they were active.
 */
final readonly class BillingCycle
{
    private function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}

    public static function current(): self
    {
        return self::containing(CarbonImmutable::now());
    }

    public static function containing(CarbonInterface $date): self
    {
        $date = CarbonImmutable::instance($date);

        return new self($date->startOfMonth(), $date->endOfMonth());
    }

    /**
     * The day the company is invoiced.
     */
    public function billsOn(): CarbonImmutable
    {
        return $this->end->startOfDay();
    }

    public function daysInMonth(): int
    {
        return $this->start->daysInMonth;
    }

    /**
     * Days left in the cycle, counting the given day itself.
     */
    public function daysRemainingFrom(CarbonInterface $date): int
    {
        $day = CarbonImmutable::instance($date)->startOfDay();

        if ($day->greaterThan($this->end)) {
            return 0;
        }

        if ($day->lessThan($this->start)) {
            return $this->daysInMonth();
        }

        return (int) $day->diffInDays($this->end->startOfDay()) + 1;
    }

    /**
     * The share of a monthly amount that applies from the given day to the
     * end of the cycle. Rounded up so a seat never starts on zero credits.
     */
    public function prorate(int $monthlyAmount, CarbonInterface $from): int
    {
        return (int) ceil($monthlyAmount * $this->daysRemainingFrom($from) / $this->daysInMonth());
    }

    public function contains(CarbonInterface $date): bool
    {
        return CarbonImmutable::instance($date)->between($this->start, $this->end);
    }

    /**
     * @return array{start: string, end: string, bills_on: string}
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
            'bills_on' => $this->billsOn()->toDateString(),
        ];
    }
}
