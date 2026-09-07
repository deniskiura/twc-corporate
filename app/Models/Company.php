<?php

namespace App\Models;

use App\Enums\SponsorshipStatus;
use App\Enums\UserRole;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * @return HasMany<User, $this>
     */
    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::CompanyAdmin);
    }

    /**
     * Every seat the company has ever invited, including revoked ones.
     *
     * @return HasMany<Sponsorship, $this>
     */
    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    /**
     * The seats an admin manages: invited and joined, but not revoked.
     *
     * @return HasMany<Sponsorship, $this>
     */
    public function team(): HasMany
    {
        return $this->sponsorships()->whereNot('status', SponsorshipStatus::Revoked);
    }
}
