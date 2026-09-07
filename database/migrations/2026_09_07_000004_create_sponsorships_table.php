<?php

use App\Enums\SponsorshipStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A sponsorship is one invited seat: it starts as an invite and becomes a
     * managed user once accepted. Keeping both in one row means the admin's
     * team list is a single query.
     */
    public function up(): void
    {
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->foreignId('invited_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('invite_token', 64)->unique();
            $table->string('status')->default(SponsorshipStatus::Invited->value);
            $table->timestamp('invited_at');
            $table->timestamp('last_sent_at');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsorships');
    }
};
