<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Credits are a ledger rather than a counter so allowances, top-ups and
     * bookings can be audited and reconciled against the monthly invoice.
     */
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->integer('amount')->comment('Positive adds credits, negative spends them');
            $table->timestamp('occurred_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
