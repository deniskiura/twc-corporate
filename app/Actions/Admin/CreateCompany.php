<?php

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyAdminWelcome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateCompany
{
    /**
     * Set a company up with its first admin, who is emailed a link to choose
     * their password. Done in one transaction so a failed email leaves no
     * half-created company behind.
     */
    public function handle(string $name, string $adminName, string $adminEmail): Company
    {
        return DB::transaction(function () use ($name, $adminName, $adminEmail) {
            $company = Company::create(['name' => $name]);

            $admin = User::forceCreate([
                'name' => $adminName,
                'email' => Str::lower($adminEmail),
                'password' => Str::password(),
                'role' => UserRole::CompanyAdmin,
                'company_id' => $company->id,
                'api_token' => Str::random(60),
                // Staff vouch for the address, and the email link proves it anyway.
                'email_verified_at' => now(),
            ]);

            $admin->notify(new CompanyAdminWelcome(Password::createToken($admin), $company));

            return $company;
        });
    }
}
