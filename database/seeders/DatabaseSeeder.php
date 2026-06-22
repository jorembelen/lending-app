<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Legacy system roles/users (super admin, admin, user roles + original staff accounts)
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);

        // Lenpro-specific roles, office staff, and collectors
        $this->call(RolesAndUsersSeeder::class);

        // Global settings, rate presets, holiday calendar
        $this->call(SettingsAndPresetsSeeder::class);

        // Loyalty tiers and rebate rules
        $this->call(LoyaltyAndRebateSeeder::class);

        // Full borrower lifecycle demo data (~48 borrowers)
        $this->call(BorrowerLifecycleSeeder::class);

        // Cash turnover rows per collector per day
        $this->call(CashTurnoverSeeder::class);
    }
}
