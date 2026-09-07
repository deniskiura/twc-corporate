<?php

namespace App\Support;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use Illuminate\Support\Collection;

/**
 * Where a user's company credits stand within one billing cycle.
 */
final readonly class CreditSummary
{
    /**
     * @param  int  $granted  Credits the company put on the seat this cycle.
     * @param  int  $used  Credits booked against them.
     * @param  int  $expired  Credits taken back, for example by a suspension.
     */
    public function __construct(
        public int $granted,
        public int $used,
        public int $expired = 0,
    ) {}

    /**
     * A seat that has not started yet: the plan's full allowance, nothing used.
     */
    public static function unused(int $allowance): self
    {
        return new self(granted: $allowance, used: 0);
    }

    /**
     * @param  Collection<int, CreditTransaction>  $transactions  Transactions from a single billing cycle.
     */
    public static function fromTransactions(Collection $transactions): self
    {
        $total = fn (CreditTransactionType $type): int => (int) $transactions
            ->where('type', $type)
            ->sum('amount');

        return new self(
            granted: $total(CreditTransactionType::Allowance),
            used: -$total(CreditTransactionType::Spend),
            expired: -$total(CreditTransactionType::Expiry),
        );
    }

    /**
     * What the company has given this cycle and not taken back.
     */
    public function allowance(): int
    {
        return max(0, $this->granted - $this->expired);
    }

    public function remaining(): int
    {
        return max(0, $this->allowance() - $this->used);
    }

    public function isExhausted(): bool
    {
        return $this->allowance() > 0 && $this->used >= $this->allowance();
    }

    /**
     * @return array{allowance: int, used: int, remaining: int, exhausted: bool}
     */
    public function toArray(): array
    {
        return [
            'allowance' => $this->allowance(),
            'used' => $this->used,
            'remaining' => $this->remaining(),
            'exhausted' => $this->isExhausted(),
        ];
    }
}
